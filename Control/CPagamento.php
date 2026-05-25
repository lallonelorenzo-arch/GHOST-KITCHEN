<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

class CPagamento
{
    public function avviaPagamento(string $tipoPrenotazione, int $idPrenotazione, string $tipoPagamento): array
    {
        if ($idPrenotazione <= 0) {
            throw new InvalidArgumentException('ID prenotazione non valido.');
        }

        $tipoPrenotazione = $this->normalizzaTipoPrenotazione($tipoPrenotazione);
        $tipoPagamento = $this->normalizzaTipoPagamento($tipoPagamento);

        $prenotazione = $tipoPrenotazione === 'chef'
            ? FPersistentManager::loadPrenotazioneChef($idPrenotazione)
            : FPersistentManager::loadPrenotazioneGhostKitchen($idPrenotazione);

        if ($prenotazione === null) {
            return ['errore' => 'Prenotazione non trovata'];
        }

        return [
            'tipoPrenotazione' => $tipoPrenotazione,
            'idPrenotazione' => $idPrenotazione,
            'tipoPagamento' => $tipoPagamento,
            'importo' => FPersistentManager::calcolaImportoPagamento($tipoPrenotazione, $idPrenotazione, $tipoPagamento),
            'metodiDisponibili' => FPersistentManager::loadMetodiPagamentoByUtente((int) $prenotazione->getIdRichiedente())
        ];
    }

    public function selezionaMetodoPagamento(int $idMetodoPagamento): array
    {
        if ($idMetodoPagamento <= 0) {
            throw new InvalidArgumentException('ID metodo pagamento non valido.');
        }

        $metodo = FPersistentManager::loadMetodoPagamento($idMetodoPagamento);
        if ($metodo === null) {
            return ['errore' => 'Metodo pagamento non trovato'];
        }

        return ['metodoPagamento' => $metodo];
    }

    public function confermaPagamento(array $datiPagamento): array
    {
        $tipoPrenotazione = $this->normalizzaTipoPrenotazione((string) ($datiPagamento['tipoPrenotazione'] ?? ''));
        $idPrenotazione = (int) ($datiPagamento['idPrenotazione'] ?? 0);
        $tipoPagamento = $this->normalizzaTipoPagamento((string) ($datiPagamento['tipoPagamento'] ?? ''));
        $idMetodoPagamento = (int) ($datiPagamento['idMetodoPagamento'] ?? 0);

        if ($idPrenotazione <= 0 || $idMetodoPagamento <= 0) {
            throw new InvalidArgumentException('Dati pagamento non validi.');
        }

        $metodo = FPersistentManager::loadMetodoPagamento($idMetodoPagamento);
        if ($metodo === null) {
            return ['errore' => 'Metodo pagamento non trovato'];
        }

        $importo = FPersistentManager::calcolaImportoPagamento($tipoPrenotazione, $idPrenotazione, $tipoPagamento);

        $pagamento = new EPagamento(
            null,
            $idPrenotazione,
            $tipoPrenotazione,
            $idMetodoPagamento,
            $importo,
            $tipoPagamento,
            EPagamento::STATO_IN_ATTESA,
            'TX-' . $idPrenotazione . '-' . time(),
            date('Y-m-d')
        );

        $pagamento->autorizza();
        $pagamento->completa();
        $pagamentoSalvato = FPersistentManager::storePagamento($pagamento);
        FPersistentManager::updatePagamento($pagamentoSalvato);

        return [
            'esito' => 'pagamento_completato',
            'pagamento' => $pagamentoSalvato,
            'importo' => $importo
        ];
    }

    private function normalizzaTipoPrenotazione(string $tipoPrenotazione): string
    {
        $tipoPrenotazione = strtolower(trim($tipoPrenotazione));
        if (!in_array($tipoPrenotazione, ['chef', 'ghost_kitchen'], true)) {
            throw new InvalidArgumentException('Tipo prenotazione non valido.');
        }

        return $tipoPrenotazione;
    }

    private function normalizzaTipoPagamento(string $tipoPagamento): string
    {
        $tipoPagamento = strtolower(trim($tipoPagamento));
        $tipiAmmessi = [EPagamento::TIPO_CAPARRA, EPagamento::TIPO_SALDO, EPagamento::TIPO_TOTALE, EPagamento::TIPO_PENALE];
        if (!in_array($tipoPagamento, $tipiAmmessi, true)) {
            throw new InvalidArgumentException('Tipo pagamento non valido.');
        }

        return $tipoPagamento;
    }
}

