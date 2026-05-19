<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

class CSegnalazione
{
    public static function avviaSegnalazione(int $idSegnalante, string $tipoTarget, int $idTarget): array
    {
        self::validaId($idSegnalante, 'ID segnalante non valido.');
        self::validaTipoTarget($tipoTarget);
        self::validaId($idTarget, 'ID target non valido.');

        $segnalante = FPersistentManager::loadUtente($idSegnalante);
        $target = FPersistentManager::loadTargetSegnalazione($tipoTarget, $idTarget);

        if ($segnalante === null) {
            return ['errore' => 'Segnalante non trovato.'];
        }
        if ($target === null) {
            return ['errore' => 'Target segnalazione non trovato.'];
        }

        return [
            'segnalante' => $segnalante,
            'target' => $target,
            'campi' => [
                'idSegnalante' => $idSegnalante,
                'tipoTarget' => $tipoTarget,
                'idTarget' => $idTarget,
                'motivo' => '',
                'descrizione' => ''
            ],
            'azioni' => [
                'inviaSegnalazione' => '/Segnalazione/inviaSegnalazione'
            ]
        ];
    }

    public static function inviaSegnalazione(array $datiSegnalazione): array
    {
        $idSegnalante = (int) ($datiSegnalazione['idSegnalante'] ?? 0);
        $tipoTarget = strtolower(trim((string) ($datiSegnalazione['tipoTarget'] ?? '')));
        $idTarget = (int) ($datiSegnalazione['idTarget'] ?? 0);
        $motivo = trim((string) ($datiSegnalazione['motivo'] ?? ''));
        $descrizione = trim((string) ($datiSegnalazione['descrizione'] ?? ''));

        self::validaId($idSegnalante, 'ID segnalante non valido.');
        self::validaTipoTarget($tipoTarget);
        self::validaId($idTarget, 'ID target non valido.');
        if ($motivo === '') {
            throw new InvalidArgumentException('Motivo segnalazione obbligatorio.');
        }

        if (FPersistentManager::loadUtente($idSegnalante) === null) {
            return ['errore' => 'Segnalante non trovato.'];
        }
        if (FPersistentManager::loadTargetSegnalazione($tipoTarget, $idTarget) === null) {
            return ['errore' => 'Target segnalazione non trovato.'];
        }

        $segnalazione = new ESegnalazione(null, $idSegnalante, $tipoTarget, $idTarget, $motivo, $descrizione, ESegnalazione::STATO_APERTA, date('Y-m-d'));
        $segnalazione = FPersistentManager::storeSegnalazione($segnalazione);

        return [
            'segnalazione' => $segnalazione,
            'messaggio' => 'Segnalazione registrata.'
        ];
    }

    private static function validaTipoTarget(string $tipoTarget): void
    {
        $ammessi = ['utente', 'chef', 'ghost_kitchen', 'recensione', 'menu'];
        if (!in_array($tipoTarget, $ammessi, true)) {
            throw new InvalidArgumentException('Tipo target segnalazione non valido.');
        }
    }

    private static function validaId(int $id, string $messaggio): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException($messaggio);
        }
    }
}
