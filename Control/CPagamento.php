<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

class CPagamento
{
    public function avviaPagamento(string $tipoPrenotazione, int $idPrenotazione): array
    {
        if ($idPrenotazione <= 0) {
            throw new InvalidArgumentException('ID prenotazione non valido.');
        }

        $tipoPrenotazione = $this->normalizzaTipoPrenotazione($tipoPrenotazione);

        $prenotazione = $tipoPrenotazione === 'chef'
            ? FPersistentManager::loadPrenotazioneChef($idPrenotazione)
            : FPersistentManager::loadPrenotazioneGhostKitchen($idPrenotazione);

        if ($prenotazione === null) {
            return ['errore' => 'Prenotazione non trovata'];
        }

        return [
            'tipoPrenotazione' => $tipoPrenotazione,
            'idPrenotazione' => $idPrenotazione,
            'importo' => FPersistentManager::calcolaImportoPagamento($tipoPrenotazione, $idPrenotazione),
            'idRichiedente' => (int) $prenotazione->getIdRichiedente(),
        ];
    }

    public function confermaPagamento(array $datiPagamento): array
    {
        $tipoPrenotazione = $this->normalizzaTipoPrenotazione((string) ($datiPagamento['tipoPrenotazione'] ?? ''));
        $idPrenotazione = (int) ($datiPagamento['idPrenotazione'] ?? 0);
        $idUtente = (int) ($datiPagamento['idUtente'] ?? 0);

        if ($idPrenotazione <= 0 || $idUtente <= 0) {
            throw new InvalidArgumentException('Dati pagamento non validi.');
        }

        $prenotazione = $tipoPrenotazione === 'chef'
            ? FPersistentManager::loadPrenotazioneChef($idPrenotazione)
            : FPersistentManager::loadPrenotazioneGhostKitchen($idPrenotazione);
        if ($prenotazione === null) {
            return ['errore' => 'Prenotazione non trovata'];
        }
        if ((int) $prenotazione->getIdRichiedente() !== $idUtente) {
            return ['errore' => 'Prenotazione non associata all utente indicato'];
        }

        $importo = FPersistentManager::calcolaImportoPagamento($tipoPrenotazione, $idPrenotazione);

        $pagamento = new EPagamento(
            null,
            $idPrenotazione,
            $tipoPrenotazione,
            $importo,
            EPagamento::STATO_COMPLETATO,
            'TX-' . $idPrenotazione . '-' . time(),
            date('Y-m-d')
        );

        $pagamentoSalvato = FPersistentManager::storePagamento($pagamento);
        if ($pagamentoSalvato === false) {
            return ['errore' => 'Pagamento non salvato. Riprova piu tardi.'];
        }

        $prenotazione->setStato(EPrenotazione::STATO_PAGATA);
        if ($tipoPrenotazione === 'chef') {
            FPersistentManager::updatePrenotazioneChef($prenotazione);
        } else {
            FPersistentManager::updatePrenotazioneGhostKitchen($prenotazione);
        }

        return [
            'esito' => 'pagamento_completato',
            'pagamento' => $pagamentoSalvato,
            'importo' => $importo
        ];
    }

    public function mostraPagamentoWeb(string $tipoPrenotazione, int $idPrenotazione, array $accesso, array $query = []): array
    {
        if (!$this->isLogged($accesso)) {
            return [
                'accessoRichiesto' => true,
                'messaggioAccesso' => 'Accedi per completare il pagamento.',
                'tipoPrenotazione' => $this->normalizzaTipoPrenotazione($tipoPrenotazione),
                'idPrenotazione' => $idPrenotazione,
            ];
        }

        $tipoPrenotazione = $this->normalizzaTipoPrenotazione($tipoPrenotazione);
        $data = $this->avviaPagamento($tipoPrenotazione, $idPrenotazione);
        $data['accesso'] = $accesso;
        $data['form'] = [];
        $data['pagamento'] = null;

        if (isset($data['errore'])) {
            return $data;
        }

        if (!$this->utentePossiedePrenotazione($tipoPrenotazione, $idPrenotazione, (int) $accesso['idUtente'])) {
            return ['errore' => 'Prenotazione non collegata al tuo profilo.'];
        }

        return $data;
    }

    public function confermaPagamentoWeb(string $tipoPrenotazione, int $idPrenotazione, array $accesso, array $post): array
    {
        $data = $this->mostraPagamentoWeb($tipoPrenotazione, $idPrenotazione, $accesso, $post);
        $data['form'] = $post;

        if (!empty($data['accessoRichiesto']) || isset($data['errore'])) {
            return $data;
        }

        try {
            $result = $this->confermaPagamento([
                'tipoPrenotazione' => $tipoPrenotazione,
                'idPrenotazione' => $idPrenotazione,
                'idUtente' => (int) ($accesso['idUtente'] ?? 0),
            ]);

            if (isset($result['errore'])) {
                $data['erroreForm'] = $result['errore'];
                return $data;
            }

            $data['pagamento'] = $result['pagamento'] ?? null;
            $data['importo'] = $result['importo'] ?? ($data['importo'] ?? 0);
            $data['messaggioSuccesso'] = 'Pagamento completato correttamente.';
            return $data;
        } catch (InvalidArgumentException $exception) {
            $data['erroreForm'] = $exception->getMessage();
            return $data;
        } catch (Throwable $exception) {
            error_log('[CPagamento] ' . $exception->getMessage());
            $data['erroreForm'] = 'Non e stato possibile completare il pagamento. Riprova piu tardi.';
            return $data;
        }
    }

    private function isLogged(array $accesso): bool
    {
        return ($accesso['isLogged'] ?? false) === true && (int) ($accesso['idUtente'] ?? 0) > 0;
    }

    private function utentePossiedePrenotazione(string $tipoPrenotazione, int $idPrenotazione, int $idUtente): bool
    {
        $prenotazione = $tipoPrenotazione === 'chef'
            ? FPersistentManager::loadPrenotazioneChef($idPrenotazione)
            : FPersistentManager::loadPrenotazioneGhostKitchen($idPrenotazione);

        return $prenotazione !== null && (int) $prenotazione->getIdRichiedente() === $idUtente;
    }

    private function normalizzaTipoPrenotazione(string $tipoPrenotazione): string
    {
        $tipoPrenotazione = strtolower(trim($tipoPrenotazione));
        if (!in_array($tipoPrenotazione, ['chef', 'ghost_kitchen'], true)) {
            throw new InvalidArgumentException('Tipo prenotazione non valido.');
        }

        return $tipoPrenotazione;
    }
}

