<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

class CGestioneRichieste
{
    public static function visualizzaRichieste(string $tipoOwner, int $idOwner): array
    {
        if ($idOwner <= 0) {
            throw new InvalidArgumentException('ID owner non valido.');
        }

        $tipoOwner = strtolower(trim($tipoOwner));
        if (!in_array($tipoOwner, ['chef', 'gestore'], true)) {
            throw new InvalidArgumentException('tipoOwner non valido.');
        }

        $richieste = $tipoOwner === 'chef'
            ? FPersistentManager::loadRichiestePrenotazioneChef($idOwner)
            : FPersistentManager::loadRichiestePrenotazioneGhostKitchenByGestore($idOwner);

        return ['tipoOwner' => $tipoOwner, 'richieste' => $richieste];
    }

    public static function accettaRichiesta(string $tipoPrenotazione, int $idPrenotazione): array
    {
        if ($idPrenotazione <= 0) {
            throw new InvalidArgumentException('ID prenotazione non valido.');
        }

        $tipoPrenotazione = self::normalizzaTipoPrenotazione($tipoPrenotazione);
        if ($tipoPrenotazione === 'chef') {
            $prenotazione = FPersistentManager::loadPrenotazioneChef($idPrenotazione);
            if ($prenotazione === null) {
                return ['errore' => 'Prenotazione chef non trovata'];
            }
            $prenotazione->accetta();
            FPersistentManager::updatePrenotazioneChef($prenotazione);
        } else {
            $prenotazione = FPersistentManager::loadPrenotazioneGhostKitchen($idPrenotazione);
            if ($prenotazione === null) {
                return ['errore' => 'Prenotazione ghost kitchen non trovata'];
            }
            $prenotazione->accetta();
            FPersistentManager::updatePrenotazioneGhostKitchen($prenotazione);
        }

        return ['messaggio' => 'Richiesta accettata', 'prenotazione' => $prenotazione];
    }

    public static function rifiutaRichiesta(string $tipoPrenotazione, int $idPrenotazione, string $motivo = ''): array
    {
        if ($idPrenotazione <= 0) {
            throw new InvalidArgumentException('ID prenotazione non valido.');
        }

        $tipoPrenotazione = self::normalizzaTipoPrenotazione($tipoPrenotazione);
        $motivo = trim($motivo);

        if ($tipoPrenotazione === 'chef') {
            $prenotazione = FPersistentManager::loadPrenotazioneChef($idPrenotazione);
            if ($prenotazione === null) {
                return ['errore' => 'Prenotazione chef non trovata'];
            }
            if ($motivo !== '') {
                $prenotazione->setNote(trim($prenotazione->getNote() . ' | Rifiuto: ' . $motivo));
            }
            $prenotazione->rifiuta();
            FPersistentManager::updatePrenotazioneChef($prenotazione);
        } else {
            $prenotazione = FPersistentManager::loadPrenotazioneGhostKitchen($idPrenotazione);
            if ($prenotazione === null) {
                return ['errore' => 'Prenotazione ghost kitchen non trovata'];
            }
            if ($motivo !== '') {
                $prenotazione->setNote(trim($prenotazione->getNote() . ' | Rifiuto: ' . $motivo));
            }
            $prenotazione->rifiuta();
            FPersistentManager::updatePrenotazioneGhostKitchen($prenotazione);
        }

        return ['messaggio' => 'Richiesta rifiutata', 'prenotazione' => $prenotazione, 'motivo' => $motivo];
    }

    private static function normalizzaTipoPrenotazione(string $tipoPrenotazione): string
    {
        $tipoPrenotazione = strtolower(trim($tipoPrenotazione));
        if (!in_array($tipoPrenotazione, ['chef', 'ghost_kitchen'], true)) {
            throw new InvalidArgumentException('tipoPrenotazione non valido.');
        }

        return $tipoPrenotazione;
    }
}
