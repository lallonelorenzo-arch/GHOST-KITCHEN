<?php
declare(strict_types=1);

class EPagamento
{
    public const PRENOTAZIONE_CHEF = 'chef';
    public const PRENOTAZIONE_GHOST_KITCHEN = 'ghost_kitchen';

    public const STATO_COMPLETATO = 'completato';

    private ?int $idPagamento; //  null prima del salvataggio nel db
    private ?int $idPrenotazione;
    private string $tipoPrenotazione;
    private float $importo;
    private string $stato; // stato del pagamento (può essere solo completato)
    private string $codiceTransazione;
    private string $dataPagamento;

    public function __construct( 
        ?int $idPagamento = null, 
        ?int $idPrenotazione = null, 
        string $tipoPrenotazione = self::PRENOTAZIONE_CHEF,
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
        $this->setCodiceTransazione($codiceTransazione);
        $this->setDataPagamento($dataPagamento);
    }

    public function getIdPagamento(): ?int { return $this->idPagamento; }

    public function setIdPagamento(?int $idPagamento): void
    {
        if ($idPagamento !== null && $idPagamento <= 0) {
            throw new InvalidArgumentException('ID pagamento non valido.');
        }
        $this->idPagamento = $idPagamento;
    }

    public function getIdPrenotazione(): ?int { return $this->idPrenotazione; } 
    public function setIdPrenotazione(?int $idPrenotazione): void 
    {
        if ($idPrenotazione !== null && $idPrenotazione <= 0) {
            throw new InvalidArgumentException('ID prenotazione non valido.');
        }
        $this->idPrenotazione = $idPrenotazione;
    }

    public function getTipoPrenotazione(): string { return $this->tipoPrenotazione; }
    public function setTipoPrenotazione(string $tipoPrenotazione): void
    {
        $tipoPrenotazione = strtolower(trim($tipoPrenotazione));
        $ammessi = [self::PRENOTAZIONE_CHEF, self::PRENOTAZIONE_GHOST_KITCHEN];
        if (!in_array($tipoPrenotazione, $ammessi, true)) {  //se $tipoPrenotazione non è presente dentro l’array $ammessi entra nell’if
            throw new InvalidArgumentException('Tipo prenotazione pagamento non valido.');
        }
        $this->tipoPrenotazione = $tipoPrenotazione;
    }

    public function getImporto(): float { return $this->importo; }
    public function setImporto(float $importo): void
    {
        if ($importo < 0) {
            throw new InvalidArgumentException('Importo pagamento non valido.');
        }
        $this->importo = round($importo, 2);
    }

    public function getStato(): string { return $this->stato; }
    public function setStato(string $stato): void
    {
        $stato = strtolower(trim($stato));
        if ($stato !== self::STATO_COMPLETATO) {
            throw new InvalidArgumentException('Stato pagamento non valido.');
        }
        $this->stato = $stato;
    }

    public function getCodiceTransazione(): string { return $this->codiceTransazione; }
    public function setCodiceTransazione(string $codiceTransazione): void { $this->codiceTransazione = trim($codiceTransazione); }

    public function getDataPagamento(): string { return $this->dataPagamento; }
    public function setDataPagamento(string $dataPagamento): void { $this->dataPagamento = trim($dataPagamento); }

    public function isCompletato(): bool { return $this->stato === self::STATO_COMPLETATO; }

    public function toArray(): array
    {
        return [
            'idPagamento' => $this->idPagamento,
            'idPrenotazione' => $this->idPrenotazione,
            'tipoPrenotazione' => $this->tipoPrenotazione,
            'importo' => $this->importo,
            'stato' => $this->stato,
            'codiceTransazione' => $this->codiceTransazione,
            'dataPagamento' => $this->dataPagamento
        ];
    }

    public function __toString(): string
    {
        return 'Pagamento #' . ($this->idPagamento ?? 'nuovo') . ' [' . $this->stato . ']';
    }
}
