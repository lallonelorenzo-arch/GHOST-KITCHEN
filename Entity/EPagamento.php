<?php
declare(strict_types=1);

class EPagamento
{
    public const PRENOTAZIONE_CHEF = 'chef';
    public const PRENOTAZIONE_GHOST_KITCHEN = 'ghost_kitchen';

    public const TIPO_CAPARRA = 'caparra';
    public const TIPO_SALDO = 'saldo';
    public const TIPO_TOTALE = 'totale';
    public const TIPO_PENALE = 'penale';

    public const STATO_IN_ATTESA = 'in_attesa';
    public const STATO_AUTORIZZATO = 'autorizzato';
    public const STATO_COMPLETATO = 'completato';
    public const STATO_FALLITO = 'fallito';
    public const STATO_RIMBORSATO = 'rimborsato';
    public const STATO_PARZIALMENTE_RIMBORSATO = 'parzialmente_rimborsato';

    private ?int $idPagamento;
    private ?int $idPrenotazione;
    private string $tipoPrenotazione;
    private ?int $idMetodoPagamento;
    private float $importo;
    private string $tipoPagamento;
    private string $stato;
    private string $codiceTransazione;
    private string $dataPagamento;

    public function __construct(
        ?int $idPagamento = null,
        ?int $idPrenotazione = null,
        string $tipoPrenotazione = self::PRENOTAZIONE_CHEF,
        ?int $idMetodoPagamento = null,
        float $importo = 0.0,
        string $tipoPagamento = self::TIPO_TOTALE,
        string $stato = self::STATO_IN_ATTESA,
        string $codiceTransazione = '',
        string $dataPagamento = ''
    ) {
        $this->setIdPagamento($idPagamento);
        $this->setIdPrenotazione($idPrenotazione);
        $this->setTipoPrenotazione($tipoPrenotazione);
        $this->setIdMetodoPagamento($idMetodoPagamento);
        $this->setImporto($importo);
        $this->setTipoPagamento($tipoPagamento);
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
        if (!in_array($tipoPrenotazione, $ammessi, true)) {
            throw new InvalidArgumentException('Tipo prenotazione pagamento non valido.');
        }
        $this->tipoPrenotazione = $tipoPrenotazione;
    }

    public function getIdMetodoPagamento(): ?int { return $this->idMetodoPagamento; }
    public function setIdMetodoPagamento(?int $idMetodoPagamento): void
    {
        if ($idMetodoPagamento !== null && $idMetodoPagamento <= 0) {
            throw new InvalidArgumentException('ID metodo pagamento non valido.');
        }
        $this->idMetodoPagamento = $idMetodoPagamento;
    }

    public function getImporto(): float { return $this->importo; }
    public function setImporto(float $importo): void
    {
        if ($importo < 0) {
            throw new InvalidArgumentException('Importo pagamento non valido.');
        }
        $this->importo = round($importo, 2);
    }

    public function getTipoPagamento(): string { return $this->tipoPagamento; }
    public function setTipoPagamento(string $tipoPagamento): void
    {
        $tipoPagamento = strtolower(trim($tipoPagamento));
        $ammessi = [self::TIPO_CAPARRA, self::TIPO_SALDO, self::TIPO_TOTALE, self::TIPO_PENALE];
        if (!in_array($tipoPagamento, $ammessi, true)) {
            throw new InvalidArgumentException('Tipo pagamento non valido.');
        }
        $this->tipoPagamento = $tipoPagamento;
    }

    public function getStato(): string { return $this->stato; }
    public function setStato(string $stato): void
    {
        $stato = strtolower(trim($stato));
        $ammessi = [
            self::STATO_IN_ATTESA,
            self::STATO_AUTORIZZATO,
            self::STATO_COMPLETATO,
            self::STATO_FALLITO,
            self::STATO_RIMBORSATO,
            self::STATO_PARZIALMENTE_RIMBORSATO
        ];
        if (!in_array($stato, $ammessi, true)) {
            throw new InvalidArgumentException('Stato pagamento non valido.');
        }
        $this->stato = $stato;
    }

    public function getCodiceTransazione(): string { return $this->codiceTransazione; }
    public function setCodiceTransazione(string $codiceTransazione): void { $this->codiceTransazione = trim($codiceTransazione); }

    public function getDataPagamento(): string { return $this->dataPagamento; }
    public function setDataPagamento(string $dataPagamento): void { $this->dataPagamento = trim($dataPagamento); }

    public function autorizza(): void
    {
        if ($this->stato !== self::STATO_IN_ATTESA) {
            throw new InvalidArgumentException('Autorizzazione consentita solo da in_attesa.');
        }
        $this->stato = self::STATO_AUTORIZZATO;
    }

    public function completa(): void
    {
        if (!in_array($this->stato, [self::STATO_AUTORIZZATO, self::STATO_IN_ATTESA], true)) {
            throw new InvalidArgumentException('Completamento consentito solo da autorizzato o in_attesa.');
        }
        $this->stato = self::STATO_COMPLETATO;
    }

    public function fallisci(): void
    {
        if (in_array($this->stato, [self::STATO_COMPLETATO, self::STATO_RIMBORSATO], true)) {
            throw new InvalidArgumentException('Impossibile fallire un pagamento completato o rimborsato.');
        }
        $this->stato = self::STATO_FALLITO;
    }

    public function segnaRimborsato(): void
    {
        if (!in_array($this->stato, [self::STATO_COMPLETATO, self::STATO_PARZIALMENTE_RIMBORSATO], true)) {
            throw new InvalidArgumentException('Rimborso totale consentito solo da completato o parzialmente_rimborsato.');
        }
        $this->stato = self::STATO_RIMBORSATO;
    }

    public function segnaParzialmenteRimborsato(): void
    {
        if ($this->stato !== self::STATO_COMPLETATO) {
            throw new InvalidArgumentException('Rimborso parziale consentito solo da completato.');
        }
        $this->stato = self::STATO_PARZIALMENTE_RIMBORSATO;
    }

    public function isCompletato(): bool { return $this->stato === self::STATO_COMPLETATO; }
    public function isRimborsato(): bool
    {
        return in_array($this->stato, [self::STATO_RIMBORSATO, self::STATO_PARZIALMENTE_RIMBORSATO], true);
    }

    public function toArray(): array
    {
        return [
            'idPagamento' => $this->idPagamento,
            'idPrenotazione' => $this->idPrenotazione,
            'tipoPrenotazione' => $this->tipoPrenotazione,
            'idMetodoPagamento' => $this->idMetodoPagamento,
            'importo' => $this->importo,
            'tipoPagamento' => $this->tipoPagamento,
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
