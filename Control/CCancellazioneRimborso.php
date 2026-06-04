<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

class CCancellazioneRimborso
{
    public function avviaCancellazione(string $tipoPrenotazione, int $idPrenotazione, int $idRichiedente): array
    {
        $this->validaTipoPrenotazione($tipoPrenotazione);
        $this->validaId($idPrenotazione, 'ID prenotazione non valido.');
        $this->validaId($idRichiedente, 'ID richiedente non valido.');

        $prenotazione = $this->loadPrenotazione($tipoPrenotazione, $idPrenotazione);
        if ($prenotazione === null) {
            return ['errore' => 'Prenotazione non trovata.'];
        }

        return [
            'tipoPrenotazione' => $tipoPrenotazione,
            'prenotazione' => $prenotazione,
            'pagamento' => FPersistentManager::loadPagamentoByPrenotazione($tipoPrenotazione, $idPrenotazione),
            'rimborsoStimato' => FPersistentManager::calcolaRimborsoStimato($tipoPrenotazione, $idPrenotazione),
            'campi' => [
                'idPrenotazione' => $idPrenotazione,
                'tipoPrenotazione' => $tipoPrenotazione,
                'idRichiedente' => $idRichiedente,
                'motivo' => ''
            ],
            'azioni' => [
                'calcolaRimborsoStimato' => '/CancellazioneRimborso/calcolaRimborsoStimato',
                'confermaCancellazione' => '/CancellazioneRimborso/confermaCancellazione'
            ]
        ];
    }

    public function calcolaRimborsoStimato(string $tipoPrenotazione, int $idPrenotazione): array
    {
        $this->validaTipoPrenotazione($tipoPrenotazione);
        $this->validaId($idPrenotazione, 'ID prenotazione non valido.');

        return FPersistentManager::calcolaRimborsoStimato($tipoPrenotazione, $idPrenotazione);
    }

    public function confermaCancellazione(array $datiCancellazione): array
    {
        $tipoPrenotazione = strtolower(trim((string) ($datiCancellazione['tipoPrenotazione'] ?? '')));
        $idPrenotazione = (int) ($datiCancellazione['idPrenotazione'] ?? 0);
        $idRichiedente = (int) ($datiCancellazione['idRichiedente'] ?? 0);
        $motivo = trim((string) ($datiCancellazione['motivo'] ?? ''));

        $this->validaTipoPrenotazione($tipoPrenotazione);
        $this->validaId($idPrenotazione, 'ID prenotazione non valido.');
        $this->validaId($idRichiedente, 'ID richiedente non valido.');
        if ($motivo === '') {
            throw new InvalidArgumentException('Motivo cancellazione obbligatorio.');
        }

        $prenotazione = $this->loadPrenotazione($tipoPrenotazione, $idPrenotazione);
        if ($prenotazione === null) {
            return ['errore' => 'Prenotazione non trovata.'];
        }
        if ((int) $prenotazione->getIdRichiedente() !== $idRichiedente) {
            return ['errore' => 'Prenotazione non collegata al richiedente indicato.'];
        }

        $stima = FPersistentManager::calcolaRimborsoStimato($tipoPrenotazione, $idPrenotazione);
        if (($stima['trovato'] ?? false) !== true) {
            return ['errore' => 'Impossibile calcolare il rimborso per la prenotazione indicata.'];
        }

        $prenotazione->cancella();

        $cancellazione = new ECancellazione(
            null,
            $idPrenotazione,
            $tipoPrenotazione,
            $idRichiedente,
            $motivo,
            date('Y-m-d'),
            (float) $stima['penale'],
            (float) $stima['importoRimborsabile'],
            ECancellazione::STATO_ACCETTATA
        );
        $cancellazione = FPersistentManager::storeCancellazione($cancellazione);
        if ($cancellazione === false) {
            return ['errore' => 'Cancellazione non salvata. Riprova piu tardi.'];
        }

        $rimborso = null;
        $pagamento = FPersistentManager::loadPagamentoByPrenotazione($tipoPrenotazione, $idPrenotazione);
        if ($pagamento !== null && (float) $stima['importoRimborsabile'] > 0) {
            if ((float) $stima['importoRimborsabile'] >= $pagamento->getImporto()) {
                $pagamento->segnaRimborsato();
            } else {
                $pagamento->segnaParzialmenteRimborsato();
            }

            $rimborso = new ERimborso(
                null,
                $pagamento->getIdPagamento(),
                $cancellazione->getIdCancellazione(),
                (float) $stima['importoRimborsabile'],
                $motivo,
                ERimborso::STATO_RICHIESTO,
                date('Y-m-d')
            );
            $rimborso = FPersistentManager::storeRimborso($rimborso);
            if ($rimborso === false) {
                return ['errore' => 'Rimborso non salvato. Riprova piu tardi.'];
            }
            FPersistentManager::updatePagamento($pagamento);
        }

        if ($tipoPrenotazione === ECancellazione::PRENOTAZIONE_CHEF) {
            FPersistentManager::updatePrenotazioneChef($prenotazione);
        } else {
            FPersistentManager::updatePrenotazioneGhostKitchen($prenotazione);
        }

        return [
            'cancellazione' => $cancellazione,
            'rimborso' => $rimborso,
            'prenotazione' => $prenotazione,
            'pagamento' => $pagamento,
            'riepilogo' => $stima
        ];
    }

    public function mostraCancellazioneWeb(string $tipoPrenotazione, int $idPrenotazione, array $accesso): array
    {
        if (!$this->isLogged($accesso)) {
            return [
                'accessoRichiesto' => true,
                'messaggioAccesso' => 'Accedi per richiedere la cancellazione.',
                'tipoPrenotazione' => $tipoPrenotazione,
                'idPrenotazione' => $idPrenotazione,
                'form' => [],
            ];
        }

        $tipoPrenotazione = $this->tipoDaSlug($tipoPrenotazione);
        $data = $this->avviaCancellazione($tipoPrenotazione, $idPrenotazione, (int) $accesso['idUtente']);
        $data['accesso'] = $accesso;
        $data['form'] = [];
        $data['cancellazione'] = null;
        $data['rimborso'] = null;

        return $data;
    }

    public function confermaCancellazioneWeb(string $tipoPrenotazione, int $idPrenotazione, array $accesso, array $post): array
    {
        $data = $this->mostraCancellazioneWeb($tipoPrenotazione, $idPrenotazione, $accesso);
        $data['form'] = $post;

        if (!empty($data['accessoRichiesto']) || isset($data['errore'])) {
            return $data;
        }

        try {
            $result = $this->confermaCancellazione([
                'tipoPrenotazione' => $this->tipoDaSlug($tipoPrenotazione),
                'idPrenotazione' => $idPrenotazione,
                'idRichiedente' => (int) $accesso['idUtente'],
                'motivo' => (string) ($post['motivo'] ?? ''),
            ]);

            if (isset($result['errore'])) {
                $data['erroreForm'] = $result['errore'];
                return $data;
            }

            $data['cancellazione'] = $result['cancellazione'] ?? null;
            $data['rimborso'] = $result['rimborso'] ?? null;
            $data['prenotazione'] = $result['prenotazione'] ?? ($data['prenotazione'] ?? null);
            $data['rimborsoStimato'] = $result['riepilogo'] ?? ($data['rimborsoStimato'] ?? []);
            $data['messaggioSuccesso'] = 'Cancellazione registrata correttamente.';
            return $data;
        } catch (InvalidArgumentException $exception) {
            $data['erroreForm'] = $exception->getMessage();
            return $data;
        } catch (Throwable $exception) {
            error_log('[CCancellazioneRimborso] ' . $exception->getMessage());
            $data['erroreForm'] = 'Non e stato possibile completare la cancellazione. Riprova piu tardi.';
            return $data;
        }
    }

    private function loadPrenotazione(string $tipoPrenotazione, int $idPrenotazione)
    {
        return $tipoPrenotazione === ECancellazione::PRENOTAZIONE_CHEF
            ? FPersistentManager::loadPrenotazioneChef($idPrenotazione)
            : FPersistentManager::loadPrenotazioneGhostKitchen($idPrenotazione);
    }

    private function validaTipoPrenotazione(string $tipoPrenotazione): void
    {
        if (!in_array($tipoPrenotazione, [ECancellazione::PRENOTAZIONE_CHEF, ECancellazione::PRENOTAZIONE_GHOST_KITCHEN], true)) {
            throw new InvalidArgumentException('Tipo prenotazione non valido.');
        }
    }

    private function tipoDaSlug(string $tipoPrenotazione): string
    {
        $tipoPrenotazione = strtolower(trim($tipoPrenotazione));
        return $tipoPrenotazione === 'ghost-kitchen' ? ECancellazione::PRENOTAZIONE_GHOST_KITCHEN : $tipoPrenotazione;
    }

    private function isLogged(array $accesso): bool
    {
        return ($accesso['isLogged'] ?? false) === true && (int) ($accesso['idUtente'] ?? 0) > 0;
    }

    private function validaId(int $id, string $messaggio): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException($messaggio);
        }
    }
}

