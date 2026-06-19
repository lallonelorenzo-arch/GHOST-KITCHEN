<?php // Apertura del file PHP.
declare(strict_types=1);

class EPagamento
{
    public const PRENOTAZIONE_CHEF = 'chef';
    public const PRENOTAZIONE_GHOST_KITCHEN = 'ghost_kitchen';

    public const STATO_COMPLETATO = 'completato';

    private ?int $idPagamento; //  null prima del salvataggio nel db
    private ?int $idPrenotazione;
    private string $tipoPrenotazione; // chef o gk
    private float $importo;
    private string $stato; // Stato del pagamento; in questa versione puo essere solo completato.
    private string $codiceTransazione; //ricevuta
    private string $dataPagamento;

    public function __construct( 
        ?int $idPagamento = null, 
        ?int $idPrenotazione = null, 
        string $tipoPrenotazione = self::PRENOTAZIONE_CHEF, // self:: riferimento alla costante definita nella classe 
        float $importo = 0.0, 
        string $stato = self::STATO_COMPLETATO, 
        string $codiceTransazione = '',
        string $dataPagamento = ''
    ) {
        $this->setIdPagamento($idPagamento);
        $this->setIdPrenotazione($idPrenotazione);
        $this->setTipoPrenotazione($tipoPrenotazione);
        $this->setImporto($importo);
        $this->setStato($stato);
        $this->setCodiceTransazione($codiceTransazione); // Normalizza e assegna il codice transazione.
        $this->setDataPagamento($dataPagamento); // Normalizza e assegna la data pagamento.
    } // Fine del costruttore.

    public function getIdPagamento(): ?int { return $this->idPagamento; } // Restituisce l'id del pagamento.
    public function setIdPagamento(?int $idPagamento): void // Imposta l'id del pagamento.
    { // Inizio del metodo setIdPagamento.
        if ($idPagamento !== null && $idPagamento <= 0) { // Controlla che l'id, se presente, sia positivo.
            throw new InvalidArgumentException('ID pagamento non valido.'); // Blocca valori non validi.
        } // Fine del controllo sull'id pagamento.
        $this->idPagamento = $idPagamento; // Assegna l'id pagamento alla proprieta.
    } // Fine del metodo setIdPagamento.

    public function getIdPrenotazione(): ?int { return $this->idPrenotazione; } // Restituisce l'id della prenotazione pagata.
    public function setIdPrenotazione(?int $idPrenotazione): void // Imposta l'id della prenotazione pagata.
    { // Inizio del metodo setIdPrenotazione.
        if ($idPrenotazione !== null && $idPrenotazione <= 0) { // Controlla che l'id, se presente, sia positivo.
            throw new InvalidArgumentException('ID prenotazione non valido.'); // Blocca valori non validi.
        } // Fine del controllo sull'id prenotazione.
        $this->idPrenotazione = $idPrenotazione; // Assegna l'id prenotazione alla proprieta.
    } // Fine del metodo setIdPrenotazione.

    public function getTipoPrenotazione(): string { return $this->tipoPrenotazione; } // Restituisce il tipo di prenotazione pagata.
    public function setTipoPrenotazione(string $tipoPrenotazione): void // Imposta il tipo di prenotazione.
    { // Inizio del metodo setTipoPrenotazione.
        $tipoPrenotazione = strtolower(trim($tipoPrenotazione)); // Normalizza il valore ricevuto.
        $ammessi = [self::PRENOTAZIONE_CHEF, self::PRENOTAZIONE_GHOST_KITCHEN]; // Elenco dei tipi di prenotazione validi.
        if (!in_array($tipoPrenotazione, $ammessi, true)) { // Verifica che il tipo sia tra quelli ammessi.
            throw new InvalidArgumentException('Tipo prenotazione pagamento non valido.'); // Blocca tipi non validi.
        } // Fine del controllo sul tipo prenotazione.
        $this->tipoPrenotazione = $tipoPrenotazione; // Assegna il tipo prenotazione alla proprieta.
    } // Fine del metodo setTipoPrenotazione.

    public function getImporto(): float { return $this->importo; } // Restituisce l'importo pagato.
    public function setImporto(float $importo): void // Imposta l'importo pagato.
    { // Inizio del metodo setImporto.
        if ($importo < 0) { // Controlla che l'importo non sia negativo.
            throw new InvalidArgumentException('Importo pagamento non valido.'); // Blocca importi non validi.
        } // Fine del controllo sull'importo.
        $this->importo = round($importo, 2); // Arrotonda a due decimali e assegna l'importo.
    } // Fine del metodo setImporto.

    public function getStato(): string { return $this->stato; } // Restituisce lo stato del pagamento.
    public function setStato(string $stato): void // Imposta lo stato del pagamento.
    { // Inizio del metodo setStato.
        $stato = strtolower(trim($stato)); // Normalizza lo stato ricevuto.
        if ($stato !== self::STATO_COMPLETATO) { // Accetta solo lo stato completato.
            throw new InvalidArgumentException('Stato pagamento non valido.'); // Blocca stati diversi da completato.
        } // Fine del controllo sullo stato.
        $this->stato = $stato; // Assegna lo stato alla proprieta.
    } // Fine del metodo setStato.

    public function getCodiceTransazione(): string { return $this->codiceTransazione; } // Restituisce il codice transazione.
    public function setCodiceTransazione(string $codiceTransazione): void { $this->codiceTransazione = trim($codiceTransazione); } // Normalizza e assegna il codice transazione.

    public function getDataPagamento(): string { return $this->dataPagamento; } // Restituisce la data del pagamento.
    public function setDataPagamento(string $dataPagamento): void { $this->dataPagamento = trim($dataPagamento); } // Normalizza e assegna la data pagamento.

    public function isCompletato(): bool { return $this->stato === self::STATO_COMPLETATO; } // Indica se il pagamento risulta completato.

    public function toArray(): array // Converte l'oggetto in array associativo.
    { // Inizio del metodo toArray.
        return [ // Inizio dell'array restituito.
            'idPagamento' => $this->idPagamento, // Inserisce l'id pagamento.
            'idPrenotazione' => $this->idPrenotazione, // Inserisce l'id prenotazione.
            'tipoPrenotazione' => $this->tipoPrenotazione, // Inserisce il tipo prenotazione.
            'importo' => $this->importo, // Inserisce l'importo.
            'stato' => $this->stato, // Inserisce lo stato.
            'codiceTransazione' => $this->codiceTransazione, // Inserisce il codice transazione.
            'dataPagamento' => $this->dataPagamento // Inserisce la data pagamento.
        ]; // Fine dell'array restituito.
    } // Fine del metodo toArray.

    public function __toString(): string // Definisce la rappresentazione testuale dell'oggetto.
    { // Inizio del metodo __toString.
        return 'Pagamento #' . ($this->idPagamento ?? 'nuovo') . ' [' . $this->stato . ']'; // Restituisce una stringa sintetica con id e stato.
    } // Fine del metodo __toString.
} // Fine della classe EPagamento.
