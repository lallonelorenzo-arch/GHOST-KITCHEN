<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

class CRecensione
{
    public static function avviaRecensione(string $tipoTarget, int $idPrenotazione, int $idAutore): array
    {
        self::validaTipoTarget($tipoTarget);
        self::validaId($idPrenotazione, 'ID prenotazione non valido.');
        self::validaId($idAutore, 'ID autore non valido.');

        $verifica = FPersistentManager::verificaPrenotazioneRecensibile($tipoTarget, $idPrenotazione, $idAutore);
        if (($verifica['recensibile'] ?? false) !== true) {
            return ['errore' => $verifica['motivo'] ?? 'Prenotazione non recensibile.'];
        }

        $prenotazione = $tipoTarget === 'chef'
            ? FPersistentManager::loadPrenotazioneChef($idPrenotazione)
            : FPersistentManager::loadPrenotazioneGhostKitchen($idPrenotazione);

        return [
            'tipoTarget' => $tipoTarget,
            'prenotazione' => $prenotazione,
            'campi' => [
                'tipoTarget' => $tipoTarget,
                'idPrenotazione' => $idPrenotazione,
                'idAutore' => $idAutore,
                'punteggio' => 5,
                'commento' => ''
            ],
            'azioni' => [
                'pubblicaRecensione' => '/Recensione/pubblicaRecensione'
            ]
        ];
    }

    public static function pubblicaRecensione(array $datiRecensione): array
    {
        $tipoTarget = strtolower(trim((string) ($datiRecensione['tipoTarget'] ?? '')));
        $idPrenotazione = (int) ($datiRecensione['idPrenotazione'] ?? 0);
        $idAutore = (int) ($datiRecensione['idAutore'] ?? 0);
        $punteggio = (int) ($datiRecensione['punteggio'] ?? 0);
        $commento = trim((string) ($datiRecensione['commento'] ?? ''));

        self::validaTipoTarget($tipoTarget);
        self::validaId($idPrenotazione, 'ID prenotazione non valido.');
        self::validaId($idAutore, 'ID autore non valido.');
        if ($punteggio < 1 || $punteggio > 5) {
            throw new InvalidArgumentException('Punteggio recensione non valido.');
        }
        if ($commento === '') {
            throw new InvalidArgumentException('Commento recensione obbligatorio.');
        }

        $verifica = FPersistentManager::verificaPrenotazioneRecensibile($tipoTarget, $idPrenotazione, $idAutore);
        if (($verifica['recensibile'] ?? false) !== true) {
            return ['errore' => $verifica['motivo'] ?? 'Prenotazione non recensibile.'];
        }

        if ($tipoTarget === 'chef') {
            $prenotazione = FPersistentManager::loadPrenotazioneChef($idPrenotazione);
            $recensione = new ERecensioneChef(null, $idAutore, $punteggio, $commento, date('Y-m-d'), ERecensione::STATO_VISIBILE, $prenotazione !== null ? $prenotazione->getIdChef() : null, $idPrenotazione);
            $recensione = FPersistentManager::storeRecensioneChef($recensione);
            $valutazione = FPersistentManager::aggiornaValutazioneChef((int) $recensione->getIdChef());
        } else {
            $prenotazione = FPersistentManager::loadPrenotazioneGhostKitchen($idPrenotazione);
            $recensione = new ERecensioneGhostKitchen(null, $idAutore, $punteggio, $commento, date('Y-m-d'), ERecensione::STATO_VISIBILE, $prenotazione !== null ? $prenotazione->getIdGhostKitchen() : null, $idPrenotazione);
            $recensione = FPersistentManager::storeRecensioneGhostKitchen($recensione);
            $valutazione = FPersistentManager::aggiornaValutazioneGhostKitchen((int) $recensione->getIdGhostKitchen());
        }

        return [
            'recensione' => $recensione,
            'valutazione' => $valutazione,
            'messaggio' => 'Recensione pubblicata.'
        ];
    }

    private static function validaTipoTarget(string $tipoTarget): void
    {
        if (!in_array($tipoTarget, ['chef', 'ghost_kitchen'], true)) {
            throw new InvalidArgumentException('Tipo target recensione non valido.');
        }
    }

    private static function validaId(int $id, string $messaggio): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException($messaggio);
        }
    }
}
