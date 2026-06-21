<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

class CPrenotazioneGhostKitchen
{
    public function avviaPrenotazioneGhostKitchen(int $idRichiedente, string $tipoRichiedente, int $idGhostKitchen): array
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

        if ($this->ghostKitchenNonPrenotabile($ghostKitchen)) {
            return ['errore' => 'Ghost kitchen non prenotabile: stato, gestore o certificazioni non approvati.'];
        }

        return [
            'richiedente' => $utente,
            'tipoRichiedente' => $tipoRichiedente,
            'ghostKitchen' => $ghostKitchen,
            'disponibilita' => FPersistentManager::loadDisponibilitaGhostKitchen($idGhostKitchen)
        ];
    }

    public function selezionaDisponibilitaGhostKitchen(int $idDisponibilitaGhostKitchen): array
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

    public function inserisciDatiPrenotazioneGhostKitchen(array $datiPrenotazione): array
    {
        $idGhostKitchen = (int) ($datiPrenotazione['idGhostKitchen'] ?? 0);
        $data = trim((string) ($datiPrenotazione['dataServizio'] ?? ''));
        $oraInizio = trim((string) ($datiPrenotazione['oraInizio'] ?? ''));
        $oraFine = trim((string) ($datiPrenotazione['oraFine'] ?? ''));

        if ($idGhostKitchen <= 0 || $data === '' || $oraInizio === '' || $oraFine === '') {
            throw new InvalidArgumentException('Dati prenotazione ghost kitchen incompleti.');
        }

        $disponibile = $this->slotLiberoCompatibile($idGhostKitchen, $data, $oraInizio, $oraFine) !== null;

        return [
            'dati' => $datiPrenotazione,
            'disponibile' => $disponibile,
            'messaggio' => $disponibile ? 'Slot disponibile' : 'Slot non disponibile'
        ];
    }

    public function confermaPrenotazioneGhostKitchen(array $datiConferma): array
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

        $ghostKitchen = FPersistentManager::loadGhostKitchen($idGhostKitchen);
        if ($ghostKitchen === null) {
            return ['errore' => 'Ghost kitchen non trovata'];
        }

        if ($this->ghostKitchenNonPrenotabile($ghostKitchen)) {
            return ['errore' => 'Ghost kitchen non prenotabile: stato, gestore o certificazioni non approvati.'];
        }

        $erroreSlot = $this->validaSlotPrenotazione($dataServizio, $oraInizio, $oraFine);
        if ($erroreSlot !== null) {
            return ['errore' => $erroreSlot];
        }

        $slotDisponibile = $this->slotLiberoCompatibile($idGhostKitchen, $dataServizio, $oraInizio, $oraFine);
        if ($slotDisponibile === null) {
            return ['errore' => 'Ghost kitchen non disponibile nello slot richiesto'];
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
        if ($prenotazioneSalvata === false) {
            return ['errore' => 'Prenotazione non salvata. Riprova piu tardi.'];
        }

        $this->occupaSlotGhostKitchen($slotDisponibile, $oraInizio, $oraFine);

        return [
            'prenotazione' => $prenotazioneSalvata,
            'azioneSuccessiva' => 'attendere_accettazione_o_avviare_pagamento',
            'urlPagamento' => '/Pagamento/avviaPagamento'
        ];
    }

    public function mostraPrenotazioneGhostKitchenWeb(int $idGhostKitchen, array $accesso): array
    {
        if ($idGhostKitchen <= 0) {
            throw new InvalidArgumentException('ID ghost kitchen non valido.');
        }

        $ghostKitchen = FPersistentManager::loadGhostKitchen($idGhostKitchen);
        if ($ghostKitchen === null) {
            return ['errore' => 'Ghost kitchen non trovata.'];
        }

        $prenotabile = !$this->ghostKitchenNonPrenotabile($ghostKitchen);
        $tipoRichiedente = $this->tipoRichiedenteDaAccesso($accesso);
        $data = [
            'ghostKitchen' => $ghostKitchen,
            'disponibilita' => FPersistentManager::loadDisponibilitaGhostKitchen($idGhostKitchen),
            'availabilityPayload' => $this->availabilityPayload($idGhostKitchen),
            'tipoRichiedente' => $tipoRichiedente,
            'accesso' => $accesso,
            'form' => [],
            'prenotazione' => null,
            'ghostKitchenPrenotabile' => $prenotabile,
        ];

        if (!$prenotabile) {
            $data['accessoRichiesto'] = true;
            $data['messaggioAccesso'] = 'Questa ghost kitchen non e prenotabile perche lo stato non e attivo oppure le certificazioni non risultano approvate e valide.';
            return $data;
        }

        if ($tipoRichiedente === null) {
            $data['accessoRichiesto'] = true;
            $data['messaggioAccesso'] = 'Accedi come cliente o chef per confermare la prenotazione. Per ora la pagina mostra i dati reali disponibili.';
        }

        return $data;
    }

    public function confermaPrenotazioneGhostKitchenWeb(int $idGhostKitchen, array $accesso, array $post): array
    {
        $data = $this->mostraPrenotazioneGhostKitchenWeb($idGhostKitchen, $accesso);
        $data['form'] = $post;

        $tipoRichiedente = $this->tipoRichiedenteDaAccesso($accesso);
        if ($tipoRichiedente === null) {
            return $data;
        }

        try {
            $result = $this->confermaPrenotazioneGhostKitchen([
                'idRichiedente' => (int) $accesso['idUtente'],
                'tipoRichiedente' => $tipoRichiedente,
                'idGhostKitchen' => $idGhostKitchen,
                'dataServizio' => (string) ($post['dataServizio'] ?? ''),
                'oraInizio' => (string) ($post['oraInizio'] ?? ''),
                'oraFine' => (string) ($post['oraFine'] ?? ''),
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
            error_log('[CPrenotazioneGhostKitchen] ' . $exception->getMessage());
            $data['erroreForm'] = 'Non e stato possibile inviare la prenotazione. Riprova piu tardi.';
            return $data;
        }
    }

    private function ghostKitchenNonPrenotabile(EGhostKitchen $ghostKitchen): bool
    {
        $gestore = FPersistentManager::loadGestore((int) $ghostKitchen->getIdGestore());

        return $ghostKitchen->getStato() !== EGhostKitchen::STATO_ATTIVA
            || $ghostKitchen->getId() === null
            || $gestore === null
            || !$gestore->isVerificato()
            || !FPersistentManager::ghostKitchenHaCertificazioniInRegola((int) $ghostKitchen->getId());
    }

    private function tipoRichiedenteDaAccesso(array $accesso): ?string
    {
        if (($accesso['isLogged'] ?? false) !== true || (int) ($accesso['idUtente'] ?? 0) <= 0) {
            return null;
        }

        $ruoli = $accesso['ruoli'] ?? [];
        $ruoloAttivo = (string) ($accesso['ruoloAttivo'] ?? '');
        if ($ruoloAttivo === 'gestore') {
            return null;
        }
        if ($ruoloAttivo === EPrenotazioneGhostKitchen::TIPO_RICHIEDENTE_CHEF || ($ruoloAttivo === '' && in_array('chef', $ruoli, true))) {
            return EPrenotazioneGhostKitchen::TIPO_RICHIEDENTE_CHEF;
        }

        if (in_array('cliente', $ruoli, true)) {
            return EPrenotazioneGhostKitchen::TIPO_RICHIEDENTE_CLIENTE;
        }

        return null;
    }

    private function occupaSlotGhostKitchen(EDisponibilitaGhostKitchen $slot, string $oraInizio, string $oraFine): void
    {
        if (!$slot->isLibera() || $slot->getIdGhostKitchen() === null || $slot->getIdDisponibilitaGhostKitchen() === null) {
            return;
        }

        $idGhostKitchen = (int) $slot->getIdGhostKitchen();
        $data = $slot->getData();
        $slotStart = substr($slot->getOraInizio(), 0, 5);
        $slotEnd = substr($slot->getOraFine(), 0, 5);
        $oraInizio = substr($oraInizio, 0, 5);
        $oraFine = substr($oraFine, 0, 5);

        if ($slotStart < $oraInizio) {
            FPersistentManager::storeDisponibilitaGhostKitchen(new EDisponibilitaGhostKitchen(null, $idGhostKitchen, $data, $slotStart, $oraInizio, EDisponibilitaGhostKitchen::STATO_LIBERA));
        }
        if ($oraFine < $slotEnd) {
            FPersistentManager::storeDisponibilitaGhostKitchen(new EDisponibilitaGhostKitchen(null, $idGhostKitchen, $data, $oraFine, $slotEnd, EDisponibilitaGhostKitchen::STATO_LIBERA));
        }

        $slot->setOraInizio($oraInizio);
        $slot->setOraFine($oraFine);
        $slot->occupa();
        FPersistentManager::updateDisponibilitaGhostKitchen($slot);
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

        if ($inizio->format('i') !== '00' || $fine->format('i') !== '00') {
            return 'Gli orari devono essere selezionati a ore piene.';
        }

        return null;
    }

    private function slotLiberoCompatibile(int $idGhostKitchen, string $data, string $oraInizio, string $oraFine): ?EDisponibilitaGhostKitchen
    {
        $data = trim($data);
        $oraInizio = substr(trim($oraInizio), 0, 5);
        $oraFine = substr(trim($oraFine), 0, 5);

        foreach (FPersistentManager::loadDisponibilitaGhostKitchen($idGhostKitchen) as $slot) {
            if (!$slot instanceof EDisponibilitaGhostKitchen || !$slot->isLibera() || $slot->getData() !== $data) {
                continue;
            }

            if (substr($slot->getOraInizio(), 0, 5) <= $oraInizio && substr($slot->getOraFine(), 0, 5) >= $oraFine) {
                return $slot;
            }
        }

        return null;
    }

    private function availabilityPayload(int $idGhostKitchen): array
    {
        $slots = array_filter(
            FPersistentManager::loadDisponibilitaGhostKitchen($idGhostKitchen),
            static fn (EDisponibilitaGhostKitchen $slot): bool => $slot->isLibera() && $slot->getData() >= date('Y-m-d')
        );

        return array_map(static fn (EDisponibilitaGhostKitchen $slot): array => [
            'date' => $slot->getData(),
            'start' => substr($slot->getOraInizio(), 0, 5),
            'end' => substr($slot->getOraFine(), 0, 5),
        ], array_values($slots));
    }

}
