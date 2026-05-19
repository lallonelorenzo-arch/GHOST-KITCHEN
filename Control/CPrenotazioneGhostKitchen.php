<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

class CPrenotazioneGhostKitchen
{
    public static function avviaPrenotazioneGhostKitchen(int $idRichiedente, string $tipoRichiedente, int $idGhostKitchen): array
    {
        if ($idRichiedente <= 0 || $idGhostKitchen <= 0) {
            throw new InvalidArgumentException('ID richiedente o ghost kitchen non valido.');
        }

        $tipoRichiedente = strtolower(trim($tipoRichiedente));
        if (!in_array($tipoRichiedente, [EPrenotazioneGhostKitchen::TIPO_RICHIEDENTE_CLIENTE, EPrenotazioneGhostKitchen::TIPO_RICHIEDENTE_CHEF], true)) {
            throw new InvalidArgumentException('Tipo richiedente non valido.');
        }

        $utente = FPersistentManager::loadUtente($idRichiedente);
        $ghostKitchen = FPersistentManager::loadGhostKitchen($idGhostKitchen);

        if ($utente === null || $ghostKitchen === null) {
            return ['errore' => 'Richiedente o ghost kitchen non trovati'];
        }

        return [
            'richiedente' => $utente,
            'tipoRichiedente' => $tipoRichiedente,
            'ghostKitchen' => $ghostKitchen,
            'disponibilita' => FPersistentManager::loadDisponibilitaGhostKitchen($idGhostKitchen)
        ];
    }

    public static function selezionaDisponibilitaGhostKitchen(int $idDisponibilitaGhostKitchen): array
    {
        if ($idDisponibilitaGhostKitchen <= 0) {
            throw new InvalidArgumentException('ID disponibilita ghost kitchen non valido.');
        }

        $disponibilita = FPersistentManager::loadDisponibilitaGhostKitchenById($idDisponibilitaGhostKitchen);
        if ($disponibilita === null) {
            return ['errore' => 'Disponibilita non trovata'];
        }

        return [
            'disponibilita' => $disponibilita,
            'isLibera' => $disponibilita->isLibera()
        ];
    }

    public static function inserisciDatiPrenotazioneGhostKitchen(array $datiPrenotazione): array
    {
        $idGhostKitchen = (int) ($datiPrenotazione['idGhostKitchen'] ?? 0);
        $data = trim((string) ($datiPrenotazione['dataServizio'] ?? ''));
        $oraInizio = trim((string) ($datiPrenotazione['oraInizio'] ?? ''));
        $oraFine = trim((string) ($datiPrenotazione['oraFine'] ?? ''));

        if ($idGhostKitchen <= 0 || $data === '' || $oraInizio === '' || $oraFine === '') {
            throw new InvalidArgumentException('Dati prenotazione ghost kitchen incompleti.');
        }

        $disponibile = FPersistentManager::verificaDisponibilitaGhostKitchen($idGhostKitchen, $data, $oraInizio, $oraFine);

        return [
            'dati' => $datiPrenotazione,
            'disponibile' => $disponibile,
            'messaggio' => $disponibile ? 'Slot disponibile' : 'Slot non disponibile'
        ];
    }

    public static function confermaPrenotazioneGhostKitchen(array $datiConferma): array
    {
        $idRichiedente = (int) ($datiConferma['idRichiedente'] ?? 0);
        $tipoRichiedente = strtolower(trim((string) ($datiConferma['tipoRichiedente'] ?? '')));
        $idGhostKitchen = (int) ($datiConferma['idGhostKitchen'] ?? 0);
        $dataServizio = trim((string) ($datiConferma['dataServizio'] ?? ''));
        $oraInizio = trim((string) ($datiConferma['oraInizio'] ?? ''));
        $oraFine = trim((string) ($datiConferma['oraFine'] ?? ''));
        $note = trim((string) ($datiConferma['note'] ?? ''));

        if ($idRichiedente <= 0 || $idGhostKitchen <= 0 || $dataServizio === '' || $oraInizio === '' || $oraFine === '') {
            throw new InvalidArgumentException('Dati conferma prenotazione ghost kitchen non validi.');
        }

        if (!in_array($tipoRichiedente, [EPrenotazioneGhostKitchen::TIPO_RICHIEDENTE_CLIENTE, EPrenotazioneGhostKitchen::TIPO_RICHIEDENTE_CHEF], true)) {
            throw new InvalidArgumentException('Tipo richiedente non valido.');
        }

        if (!FPersistentManager::verificaDisponibilitaGhostKitchen($idGhostKitchen, $dataServizio, $oraInizio, $oraFine)) {
            return ['errore' => 'Ghost kitchen non disponibile nello slot richiesto'];
        }

        $ghostKitchen = FPersistentManager::loadGhostKitchen($idGhostKitchen);
        if ($ghostKitchen === null) {
            return ['errore' => 'Ghost kitchen non trovata'];
        }

        $ore = max(1.0, (strtotime($oraFine) - strtotime($oraInizio)) / 3600);
        $importoTotale = $ghostKitchen->getPrezzoOrario() * $ore;

        $prenotazione = new EPrenotazioneGhostKitchen(
            null,
            $idRichiedente,
            date('Y-m-d'),
            $dataServizio,
            $oraInizio,
            $oraFine,
            EPrenotazione::STATO_IN_ATTESA,
            $importoTotale,
            $note,
            $idGhostKitchen,
            $tipoRichiedente
        );

        $prenotazione->validaPerConferma();
        $prenotazioneSalvata = FPersistentManager::storePrenotazioneGhostKitchen($prenotazione);

        return [
            'prenotazione' => $prenotazioneSalvata,
            'azioneSuccessiva' => 'attendere_accettazione_o_avviare_pagamento',
            'urlPagamento' => '/Pagamento/avviaPagamento'
        ];
    }
}
