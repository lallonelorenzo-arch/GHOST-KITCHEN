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

    private function validaId(int $id, string $messaggio): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException($messaggio);
        }
    }
}

