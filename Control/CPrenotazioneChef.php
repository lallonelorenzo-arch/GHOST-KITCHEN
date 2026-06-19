<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';
require_once __DIR__ . '/../Foundation/FSession.php';
require_once __DIR__ . '/CPagamento.php';

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

        if ($this->chefNonPrenotabilePerCertificazioni($idChef)) {
            return ['errore' => 'Chef non prenotabile: certificazioni non approvate o scadute.'];
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
        $abbinamentoVini = filter_var($datiConferma['abbinamentoVini'] ?? false, FILTER_VALIDATE_BOOL);

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

        if ($this->chefNonPrenotabilePerCertificazioni($idChef)) {
            return ['errore' => 'Chef non prenotabile: certificazioni non approvate o scadute.'];
        }

        $erroreSlot = $this->validaSlotPrenotazione($dataServizio, $oraInizio, $oraFine);
        if ($erroreSlot !== null) {
            return ['errore' => $erroreSlot];
        }

        if (!FPersistentManager::verificaDisponibilitaChef($idChef, $dataServizio, $oraInizio, $oraFine)) {
            return ['errore' => 'Chef non disponibile nello slot richiesto'];
        }

        $menu = FPersistentManager::loadMenu($idMenu);
        if ($menu === null || (int) $menu->getIdChef() !== $idChef || !$menu->isAttivo()) {
            return ['errore' => 'Menu non disponibile per questo chef'];
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
            $richiesteSpeciali,
            $abbinamentoVini
        );

        $prenotazione->validaPerConferma();
        $prenotazioneSalvata = FPersistentManager::storePrenotazioneChef($prenotazione);
        if ($prenotazioneSalvata === false) {
            return ['errore' => 'Prenotazione non salvata. Riprova piu tardi.'];
        }

        $this->occupaSlotChef($idChef, $dataServizio, $oraInizio, $oraFine);

        return [
            'prenotazione' => $prenotazioneSalvata,
            'azioneSuccessiva' => 'attendere_accettazione_o_avviare_pagamento',
            'urlPagamento' => '/Pagamento/avviaPagamento'
        ];
    }

    private function chefNonPrenotabilePerCertificazioni(int $idChef): bool
    {
        return !FPersistentManager::chefHaCertificazioniInRegola($idChef);
    }

    private function canPrenotareComeCliente(array $accesso): bool
    {
        if (($accesso['isLogged'] ?? false) !== true || (int) ($accesso['idUtente'] ?? 0) <= 0) {
            return false;
        }

        $ruoli = $accesso['ruoli'] ?? [];
        $ruoloAttivo = (string) ($accesso['ruoloAttivo'] ?? '');
        if ($ruoloAttivo === 'gestore' && in_array('gestore', $ruoli, true)) {
            return true;
        }
        if ($ruoloAttivo === 'chef') {
            return false;
        }

        return in_array('cliente', $ruoli, true) || (in_array('gestore', $ruoli, true) && !in_array('chef', $ruoli, true));
    }

    public function confermaPrenotazioneChefWizardWeb(int $idChef, array $accesso, array $post): array
    {
        $ritorno = '/chef/' . $idChef;
        if (!$this->canPrenotareComeCliente($accesso)) {
            return $this->esitoWizard(
                'Prenotazione non disponibile',
                'Accedi come cliente o gestore per prenotare questo chef.',
                false,
                $ritorno
            );
        }

        try {
            if (!FSession::verifyCsrfToken('chef_booking', (string) ($post['csrfToken'] ?? ''))) {
                throw new InvalidArgumentException('Sessione di prenotazione scaduta. Ricarica la pagina e riprova.');
            }

            $idMenu = filter_var($post['idMenu'] ?? null, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1],
            ]);
            if ($idMenu === false) {
                throw new InvalidArgumentException('Seleziona un menu valido.');
            }

            $fascia = strtolower(trim((string) ($post['fasciaServizio'] ?? '')));
            EDisponibilitaChef::orariPerFascia($fascia);
            $dataServizio = trim((string) ($post['dataServizio'] ?? ''));
            $slot = null;
            foreach (FPersistentManager::loadDisponibilitaChef($idChef) as $disponibilita) {
                if ($disponibilita->getData() === $dataServizio
                    && $disponibilita->isLibera()
                    && $disponibilita->getFasciaServizio() === $fascia
                ) {
                    $slot = $disponibilita;
                    break;
                }
            }
            if (!$slot instanceof EDisponibilitaChef) {
                throw new InvalidArgumentException('La disponibilita selezionata non e piu libera.');
            }
            $oraInizio = $slot->getOraInizio();
            $oraFine = $slot->getOraFine();

            $indirizzo = $this->validateWizardText((string) ($post['indirizzo'] ?? ''), 'Indirizzo', 180);
            $citta = $this->validateWizardText((string) ($post['citta'] ?? ''), 'Città', 120);
            $provincia = strtoupper($this->validateWizardText((string) ($post['provincia'] ?? ''), 'Provincia', 2));
            if (!EUtente::isProvinciaItaliana($provincia)) {
                throw new InvalidArgumentException('Seleziona una provincia valida.');
            }
            $numeroCivico = $this->validateWizardText((string) ($post['numeroCivico'] ?? ''), 'Numero civico', 20);
            $richiesteSpeciali = $this->validateWizardText(
                (string) ($post['richiesteSpeciali'] ?? ''),
                'Richieste speciali',
                2000,
                false
            );

            $numeroPersone = filter_var($post['numeroPersone'] ?? null, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1, 'max_range' => 100],
            ]);
            if ($numeroPersone === false) {
                throw new InvalidArgumentException('Il numero di partecipanti deve essere compreso tra 1 e 100.');
            }

            $abbinamentoVini = (string) ($post['abbinamentoVini'] ?? '');
            if (!in_array($abbinamentoVini, ['0', '1'], true)) {
                throw new InvalidArgumentException('Seleziona una preferenza valida per l abbinamento vini.');
            }

            $result = $this->confermaPrenotazioneChef([
                'idCliente' => (int) $accesso['idUtente'],
                'idChef' => $idChef,
                'idMenu' => $idMenu,
                'dataServizio' => $dataServizio,
                'oraInizio' => $oraInizio,
                'oraFine' => $oraFine,
                'indirizzoServizio' => $indirizzo . ' ' . $numeroCivico . ', ' . $citta . ' (' . $provincia . ')',
                'numeroPersone' => $numeroPersone,
                'richiesteSpeciali' => $richiesteSpeciali,
                'note' => 'Fascia servizio: ' . ucfirst($fascia),
                'abbinamentoVini' => $abbinamentoVini === '1',
            ]);
            if (isset($result['errore'])) {
                return $this->esitoWizard('Prenotazione non completata', (string) $result['errore'], false, $ritorno);
            }

            $prenotazione = $result['prenotazione'] ?? null;
            if (!$prenotazione instanceof EPrenotazioneChef || $prenotazione->getIdPrenotazione() === null) {
                return $this->esitoWizard('Prenotazione non completata', 'La prenotazione non e stata salvata.', false, $ritorno);
            }

            $pagamento = (new CPagamento())->confermaPagamento([
                'tipoPrenotazione' => 'chef',
                'idPrenotazione' => (int) $prenotazione->getIdPrenotazione(),
                'idUtente' => (int) ($accesso['idUtente'] ?? 0),
            ]);
            if (isset($pagamento['errore'])) {
                return $this->esitoWizard(
                    'Prenotazione creata, pagamento non completato',
                    (string) $pagamento['errore'] . ' Puoi riprovare dalla dashboard.',
                    false,
                    '/prenotazioni'
                );
            }

            return $this->esitoWizard(
                'Prenotazione confermata',
                sprintf(
                    'Pagamento completato. Servizio %s del %s prenotato per %d partecipanti.',
                    $fascia,
                    (string) $post['dataServizio'],
                    $numeroPersone
                ),
                true,
                '/prenotazioni'
            );
        } catch (InvalidArgumentException $exception) {
            return $this->esitoWizard('Prenotazione non completata', $exception->getMessage(), false, $ritorno);
        } catch (Throwable $exception) {
            error_log('[CPrenotazioneChef] ' . $exception->getMessage());
            return $this->esitoWizard('Prenotazione non completata', 'Errore interno durante la prenotazione.', false, $ritorno);
        }
    }

    private function validateWizardText(
        string $value,
        string $label,
        int $maxLength,
        bool $required = true
    ): string {
        $value = trim($value);
        if ($required && $value === '') {
            throw new InvalidArgumentException($label . ' e obbligatorio.');
        }

        $length = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
        if ($length > $maxLength) {
            throw new InvalidArgumentException($label . ' troppo lungo.');
        }
        if ($value !== '' && preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', $value) === 1) {
            throw new InvalidArgumentException($label . ' contiene caratteri non validi.');
        }

        return $value;
    }

    private function occupaSlotChef(int $idChef, string $dataServizio, string $oraInizio, string $oraFine): void
    {
        $slot = FPersistentManager::loadDisponibilitaChefBySlot($idChef, $dataServizio, $oraInizio, $oraFine);
        if ($slot === null || !$slot->isLibera()) {
            return;
        }

        $slot->occupa();
        FPersistentManager::updateDisponibilitaChef($slot);
    }

    private function validaSlotPrenotazione(string $dataServizio, string $oraInizio, string $oraFine): ?string
    {
        $giorno = DateTimeImmutable::createFromFormat('!Y-m-d', trim($dataServizio));
        if ($giorno === false || $giorno->format('Y-m-d') !== trim($dataServizio)) {
            return 'Inserisci una data servizio valida.';
        }

        if ($giorno < new DateTimeImmutable('today')) {
            return 'Non puoi prenotare uno slot nel passato.';
        }

        $inizio = DateTimeImmutable::createFromFormat('H:i', trim($oraInizio)) ?: DateTimeImmutable::createFromFormat('H:i:s', trim($oraInizio));
        $fine = DateTimeImmutable::createFromFormat('H:i', trim($oraFine)) ?: DateTimeImmutable::createFromFormat('H:i:s', trim($oraFine));
        if ($inizio === false || $fine === false || $fine <= $inizio) {
            return 'Ora fine deve essere successiva all ora inizio.';
        }

        return null;
    }

    private function esitoWizard(string $titolo, string $messaggio, bool $successo, string $ritorno): array
    {
        return [
            'titolo' => $titolo,
            'messaggio' => $messaggio,
            'successo' => $successo,
            'ritorno' => $ritorno,
        ];
    }

}
