<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

class CGestioneRichieste
{
    public function visualizzaRichieste(string $tipoOwner, int $idOwner): array
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

    public function accettaRichiesta(string $tipoPrenotazione, int $idPrenotazione): array
    {
        if ($idPrenotazione <= 0) {
            throw new InvalidArgumentException('ID prenotazione non valido.');
        }

        $tipoPrenotazione = $this->normalizzaTipoPrenotazione($tipoPrenotazione);
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

    public function rifiutaRichiesta(string $tipoPrenotazione, int $idPrenotazione, string $motivo = ''): array
    {
        if ($idPrenotazione <= 0) {
            throw new InvalidArgumentException('ID prenotazione non valido.');
        }

        $tipoPrenotazione = $this->normalizzaTipoPrenotazione($tipoPrenotazione);
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


    public function visualizzaRichiesteWeb(array $accesso): array
    {
        $data = [
            'accesso' => $accesso,
            'richiesteChef' => [],
            'richiesteGhostKitchen' => [],
        ];

        if (($accesso['isLogged'] ?? false) !== true) {
            $data['messaggioAccesso'] = 'Accedi come chef o gestore per visualizzare le richieste.';
            return $data;
        }

        $ruoli = $accesso['ruoli'] ?? [];
        if (in_array('chef', $ruoli, true)) {
            $data['richiesteChef'] = $this->visualizzaRichieste('chef', (int) $accesso['idUtente'])['richieste'];
        }

        if (in_array('gestore', $ruoli, true)) {
            $data['richiesteGhostKitchen'] = $this->visualizzaRichieste('gestore', (int) $accesso['idUtente'])['richieste'];
        }

        return $data;
    }

    public function gestisciRichiestaWeb(string $tipoPrenotazione, int $idPrenotazione, string $azione, array $accesso, array $post = []): array
    {
        if (!$this->puoGestire($tipoPrenotazione, $accesso)) {
            return $this->esito('Accesso richiesto', 'Il ruolo attivo non puo gestire questa richiesta.', false, '/richieste');
        }

        try {
            $result = $azione === 'accetta'
                ? $this->accettaRichiesta($tipoPrenotazione, $idPrenotazione)
                : $this->rifiutaRichiesta($tipoPrenotazione, $idPrenotazione, (string) ($post['motivo'] ?? ''));

            if (isset($result['errore'])) {
                return $this->esito('Richiesta non aggiornata', (string) $result['errore'], false, '/richieste');
            }

            return $this->esito('Richiesta aggiornata', (string) ($result['messaggio'] ?? 'Operazione completata.'), true, '/richieste');
        } catch (Throwable $exception) {
            return $this->esito('Richiesta non aggiornata', $exception->getMessage(), false, '/richieste');
        }
    }

    private function puoGestire(string $tipoPrenotazione, array $accesso): bool
    {
        if (($accesso['isLogged'] ?? false) !== true) {
            return false;
        }

        $ruoli = $accesso['ruoli'] ?? [];
        return ($tipoPrenotazione === 'chef' && in_array('chef', $ruoli, true))
            || ($tipoPrenotazione === 'ghost_kitchen' && in_array('gestore', $ruoli, true));
    }

    private function esito(string $titolo, string $messaggio, bool $successo, string $ritorno): array
    {
        return [
            'titolo' => $titolo,
            'messaggio' => $messaggio,
            'successo' => $successo,
            'ritorno' => $ritorno,
        ];
    }

    private function normalizzaTipoPrenotazione(string $tipoPrenotazione): string
    {
        $tipoPrenotazione = strtolower(trim($tipoPrenotazione));
        if (!in_array($tipoPrenotazione, ['chef', 'ghost_kitchen'], true)) {
            throw new InvalidArgumentException('tipoPrenotazione non valido.');
        }

        return $tipoPrenotazione;
    }
}

