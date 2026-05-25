<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

class CPrenotazioneChef
{
    public function avviaPrenotazioneChef(int $idCliente, int $idChef): array
    {
        if ($idCliente <= 0 || $idChef <= 0) {
            throw new InvalidArgumentException('ID cliente o chef non valido.');
        }

        $cliente = FPersistentManager::loadCliente($idCliente);
        $chef = FPersistentManager::loadChef($idChef);

        if ($cliente === null || $chef === null) {
            return ['errore' => 'Cliente o chef non trovato'];
        }

        return [
            'cliente' => $cliente,
            'chef' => $chef,
            'menuDisponibili' => FPersistentManager::loadMenuByChef($idChef),
            'disponibilitaChef' => FPersistentManager::loadDisponibilitaChef($idChef)
        ];
    }

    public function selezionaMenu(int $idMenu): array
    {
        if ($idMenu <= 0) {
            throw new InvalidArgumentException('ID menu non valido.');
        }

        $menu = FPersistentManager::loadMenu($idMenu);
        if ($menu === null) {
            return ['errore' => 'Menu non trovato'];
        }

        return [
            'menu' => $menu,
            'piatti' => FPersistentManager::loadPiattiByMenu($idMenu)
        ];
    }

    public function inserisciDatiPrenotazioneChef(array $datiPrenotazione): array
    {
        $idChef = (int) ($datiPrenotazione['idChef'] ?? 0);
        $data = trim((string) ($datiPrenotazione['dataServizio'] ?? ''));
        $oraInizio = trim((string) ($datiPrenotazione['oraInizio'] ?? ''));
        $oraFine = trim((string) ($datiPrenotazione['oraFine'] ?? ''));

        if ($idChef <= 0 || $data === '' || $oraInizio === '' || $oraFine === '') {
            throw new InvalidArgumentException('Dati prenotazione chef incompleti.');
        }

        $disponibile = FPersistentManager::verificaDisponibilitaChef($idChef, $data, $oraInizio, $oraFine);

        return [
            'dati' => $datiPrenotazione,
            'disponibile' => $disponibile,
            'messaggio' => $disponibile ? 'Slot disponibile' : 'Slot non disponibile'
        ];
    }

    public function confermaPrenotazioneChef(array $datiConferma): array
    {
        $idCliente = (int) ($datiConferma['idCliente'] ?? 0);
        $idChef = (int) ($datiConferma['idChef'] ?? 0);
        $idMenu = (int) ($datiConferma['idMenu'] ?? 0);
        $dataServizio = trim((string) ($datiConferma['dataServizio'] ?? ''));
        $oraInizio = trim((string) ($datiConferma['oraInizio'] ?? ''));
        $oraFine = trim((string) ($datiConferma['oraFine'] ?? ''));
        $indirizzo = trim((string) ($datiConferma['indirizzoServizio'] ?? ''));
        $numeroPersone = (int) ($datiConferma['numeroPersone'] ?? 0);
        $richiesteSpeciali = trim((string) ($datiConferma['richiesteSpeciali'] ?? ''));
        $note = trim((string) ($datiConferma['note'] ?? ''));

        if (
            $idCliente <= 0 ||
            $idChef <= 0 ||
            $idMenu <= 0 ||
            $numeroPersone <= 0 ||
            $dataServizio === '' ||
            $oraInizio === '' ||
            $oraFine === '' ||
            $indirizzo === ''
        ) {
            throw new InvalidArgumentException('Dati conferma prenotazione chef non validi.');
        }

        if (!FPersistentManager::verificaDisponibilitaChef($idChef, $dataServizio, $oraInizio, $oraFine)) {
            return ['errore' => 'Chef non disponibile nello slot richiesto'];
        }

        $menu = FPersistentManager::loadMenu($idMenu);
        if ($menu === null) {
            return ['errore' => 'Menu non trovato'];
        }

        $importoTotale = $menu->getPrezzoPersona() * $numeroPersone;

        $prenotazione = new EPrenotazioneChef(
            null,
            $idCliente,
            date('Y-m-d'),
            $dataServizio,
            $oraInizio,
            $oraFine,
            EPrenotazione::STATO_IN_ATTESA,
            $importoTotale,
            $note,
            $idChef,
            $idMenu,
            $indirizzo,
            $numeroPersone,
            $richiesteSpeciali
        );

        $prenotazione->validaPerConferma();
        $prenotazioneSalvata = FPersistentManager::storePrenotazioneChef($prenotazione);

        return [
            'prenotazione' => $prenotazioneSalvata,
            'azioneSuccessiva' => 'attendere_accettazione_o_avviare_pagamento',
            'urlPagamento' => '/Pagamento/avviaPagamento'
        ];
    }
}

