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
        if ($prenotazioneSalvata === false) {
            return ['errore' => 'Prenotazione non salvata. Riprova piu tardi.'];
        }

        return [
            'prenotazione' => $prenotazioneSalvata,
            'azioneSuccessiva' => 'attendere_accettazione_o_avviare_pagamento',
            'urlPagamento' => '/Pagamento/avviaPagamento'
        ];
    }

    public function mostraPrenotazioneChefWeb(int $idChef, array $accesso): array
    {
        if ($idChef <= 0) {
            throw new InvalidArgumentException('ID chef non valido.');
        }

        $chef = FPersistentManager::loadChef($idChef);
        if ($chef === null) {
            return ['errore' => 'Chef non trovato.'];
        }

        $data = [
            'chef' => $chef,
            'menuDisponibili' => FPersistentManager::loadMenuByChef($idChef),
            'disponibilitaChef' => FPersistentManager::loadDisponibilitaChef($idChef),
            'accesso' => $accesso,
            'form' => [],
            'prenotazione' => null,
        ];

        if (!$this->canPrenotareComeCliente($accesso)) {
            $data['accessoRichiesto'] = true;
            $data['messaggioAccesso'] = 'Accedi come cliente per confermare la prenotazione. Per ora la pagina mostra i dati reali disponibili.';
            return $data;
        }

        $cliente = FPersistentManager::loadCliente((int) $accesso['idUtente']);
        if ($cliente === null) {
            $data['accessoRichiesto'] = true;
            $data['messaggioAccesso'] = 'Il tuo utente non risulta collegato al ruolo cliente.';
            return $data;
        }

        $data['cliente'] = $cliente;
        return $data;
    }

    public function confermaPrenotazioneChefWeb(int $idChef, array $accesso, array $post): array
    {
        $data = $this->mostraPrenotazioneChefWeb($idChef, $accesso);
        $data['form'] = $post;

        if (!$this->canPrenotareComeCliente($accesso)) {
            return $data;
        }

        try {
            $result = $this->confermaPrenotazioneChef([
                'idCliente' => (int) $accesso['idUtente'],
                'idChef' => $idChef,
                'idMenu' => (int) ($post['idMenu'] ?? 0),
                'dataServizio' => (string) ($post['dataServizio'] ?? ''),
                'oraInizio' => (string) ($post['oraInizio'] ?? ''),
                'oraFine' => (string) ($post['oraFine'] ?? ''),
                'indirizzoServizio' => (string) ($post['indirizzoServizio'] ?? ''),
                'numeroPersone' => (int) ($post['numeroPersone'] ?? 0),
                'richiesteSpeciali' => (string) ($post['richiesteSpeciali'] ?? ''),
                'note' => (string) ($post['note'] ?? ''),
            ]);

            if (isset($result['errore'])) {
                $data['erroreForm'] = $result['errore'];
                return $data;
            }

            $data['prenotazione'] = $result['prenotazione'] ?? null;
            $data['messaggioSuccesso'] = 'Richiesta di prenotazione inviata. Stato: in attesa di accettazione.';
            return $data;
        } catch (InvalidArgumentException $exception) {
            $data['erroreForm'] = $exception->getMessage();
            return $data;
        } catch (Throwable $exception) {
            error_log('[CPrenotazioneChef] ' . $exception->getMessage());
            $data['erroreForm'] = 'Non e stato possibile inviare la prenotazione. Riprova piu tardi.';
            return $data;
        }
    }

    private function canPrenotareComeCliente(array $accesso): bool
    {
        return ($accesso['isLogged'] ?? false) === true
            && in_array('cliente', $accesso['ruoli'] ?? [], true)
            && (int) ($accesso['idUtente'] ?? 0) > 0;
    }

}

