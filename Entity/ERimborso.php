<?php
declare(strict_types=1);

class ERimborso
{
    public const STATO_RICHIESTO = 'richiesto';
    public const STATO_APPROVATO = 'approvato';
    public const STATO_RIFIUTATO = 'rifiutato';
    public const STATO_ESEGUITO = 'eseguito';
    public const STATO_FALLITO = 'fallito';

    private ?int $idRimborso;
    private ?int $idPagamento;
    private ?int $idCancellazione;
    private float $importo;
    private string $motivo;
    private string $stato;
    private string $dataRichiesta;
    private string $dataEsecuzione;

    public function __construct(
        ?int $idRimborso = null,
        ?int $idPagamento = null,
        ?int $idCancellazione = null,
        float $importo = 0.0,
        string $motivo = '',
        string $stato = self::STATO_RICHIESTO,
        string $dataRichiesta = '',
        string $dataEsecuzione = ''
    ) {
        $this->setIdRimborso($idRimborso);
        $this->setIdPagamento($idPagamento);
        $this->setIdCancellazione($idCancellazione);
        $this->setImporto($importo);
        $this->setMotivo($motivo);
        $this->setStato($stato);
        $this->setDataRichiesta($dataRichiesta);
        $this->setDataEsecuzione($dataEsecuzione);
    }

    public function getIdRimborso(): ?int { return $this->idRimborso; }
    public function setIdRimborso(?int $idRimborso): void
    {
        if ($idRimborso !== null && $idRimborso <= 0) {
            throw new InvalidArgumentException('ID rimborso non valido.');
        }
        $this->idRimborso = $idRimborso;
    }

    public function getIdPagamento(): ?int { return $this->idPagamento; }
    public function setIdPagamento(?int $idPagamento): void
    {
        if ($idPagamento !== null && $idPagamento <= 0) {
            throw new InvalidArgumentException('ID pagamento non valido.');
        }
        $this->idPagamento = $idPagamento;
    }

    public function getIdCancellazione(): ?int { return $this->idCancellazione; }
    public function setIdCancellazione(?int $idCancellazione): void
    {
        if ($idCancellazione !== null && $idCancellazione <= 0) {
            throw new InvalidArgumentException('ID cancellazione rimborso non valido.');
        }
        $this->idCancellazione = $idCancellazione;
    }

    public function getImporto(): float { return $this->importo; }
    public function setImporto(float $importo): void
    {
        if ($importo < 0) {
            throw new InvalidArgumentException('Importo rimborso non valido.');
        }
        $this->importo = round($importo, 2);
    }

    public function getMotivo(): string { return $this->motivo; }
    public function setMotivo(string $motivo): void { $this->motivo = trim($motivo); }

    public function getStato(): string { return $this->stato; }
    public function setStato(string $stato): void
    {
        $stato = strtolower(trim($stato));
        $ammessi = [
            self::STATO_RICHIESTO,
            self::STATO_APPROVATO,
            self::STATO_RIFIUTATO,
            self::STATO_ESEGUITO,
            self::STATO_FALLITO
        ];
        if (!in_array($stato, $ammessi, true)) {
            throw new InvalidArgumentException('Stato rimborso non valido.');
        }
        $this->stato = $stato;
    }

    public function getDataRichiesta(): string { return $this->dataRichiesta; }
    public function setDataRichiesta(string $dataRichiesta): void { $this->dataRichiesta = trim($dataRichiesta); }

    public function getDataEsecuzione(): string { return $this->dataEsecuzione; }
    public function setDataEsecuzione(string $dataEsecuzione): void { $this->dataEsecuzione = trim($dataEsecuzione); }

    public function approva(): void
    {
        if ($this->stato !== self::STATO_RICHIESTO) {
            throw new InvalidArgumentException('Approvazione rimborso consentita solo da richiesto.');
        }
        $this->stato = self::STATO_APPROVATO;
    }

    public function rifiuta(): void
    {
        if ($this->stato !== self::STATO_RICHIESTO) {
            throw new InvalidArgumentException('Rifiuto rimborso consentito solo da richiesto.');
        }
        $this->stato = self::STATO_RIFIUTATO;
    }

    public function esegui(): void
    {
        if ($this->stato !== self::STATO_APPROVATO) {
            throw new InvalidArgumentException('Esecuzione rimborso consentita solo da approvato.');
        }
        $this->stato = self::STATO_ESEGUITO;
    }

    public function fallisci(): void
    {
        if ($this->stato !== self::STATO_APPROVATO) {
            throw new InvalidArgumentException('Fallimento rimborso consentito solo da approvato.');
        }
        $this->stato = self::STATO_FALLITO;
    }
    public function isEseguito(): bool { return $this->stato === self::STATO_ESEGUITO; }

    public function toArray(): array
    {
        return [
            'idRimborso' => $this->idRimborso,
            'idPagamento' => $this->idPagamento,
            'idCancellazione' => $this->idCancellazione,
            'importo' => $this->importo,
            'motivo' => $this->motivo,
            'stato' => $this->stato,
            'dataRichiesta' => $this->dataRichiesta,
            'dataEsecuzione' => $this->dataEsecuzione
        ];
    }

    public function __toString(): string
    {
        return 'Rimborso #' . ($this->idRimborso ?? 'nuovo') .
            ' - Cancellazione #' . ($this->idCancellazione ?? 'n/d') .
            ' [' . $this->stato . ']';
    }
}
