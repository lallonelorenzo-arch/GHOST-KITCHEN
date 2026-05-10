<?php
declare(strict_types=1);

class ECancellazione
{
    public const PRENOTAZIONE_CHEF = 'chef';
    public const PRENOTAZIONE_GHOST_KITCHEN = 'ghost_kitchen';

    public const STATO_RICHIESTA = 'richiesta';
    public const STATO_ACCETTATA = 'accettata';
    public const STATO_RIFIUTATA = 'rifiutata';
    public const STATO_COMPLETATA = 'completata';

    private ?int $idCancellazione;
    private ?int $idPrenotazione;
    private string $tipoPrenotazione;
    private ?int $idRichiedente;
    private string $motivo;
    private string $dataRichiesta;
    private float $penaleApplicata;
    private float $importoRimborsato;
    private string $stato;

    public function __construct(
        ?int $idCancellazione = null,
        ?int $idPrenotazione = null,
        string $tipoPrenotazione = self::PRENOTAZIONE_CHEF,
        ?int $idRichiedente = null,
        string $motivo = '',
        string $dataRichiesta = '',
        float $penaleApplicata = 0.0,
        float $importoRimborsato = 0.0,
        string $stato = self::STATO_RICHIESTA
    ) {
        $this->setIdCancellazione($idCancellazione);
        $this->setIdPrenotazione($idPrenotazione);
        $this->setTipoPrenotazione($tipoPrenotazione);
        $this->setIdRichiedente($idRichiedente);
        $this->setMotivo($motivo);
        $this->setDataRichiesta($dataRichiesta);
        $this->setPenaleApplicata($penaleApplicata);
        $this->setImportoRimborsato($importoRimborsato);
        $this->setStato($stato);
    }

    public function getIdCancellazione(): ?int { return $this->idCancellazione; }
    public function setIdCancellazione(?int $idCancellazione): void
    {
        if ($idCancellazione !== null && $idCancellazione <= 0) {
            throw new InvalidArgumentException('ID cancellazione non valido.');
        }
        $this->idCancellazione = $idCancellazione;
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
            throw new InvalidArgumentException('Tipo prenotazione cancellazione non valido.');
        }
        $this->tipoPrenotazione = $tipoPrenotazione;
    }

    public function getIdRichiedente(): ?int { return $this->idRichiedente; }
    public function setIdRichiedente(?int $idRichiedente): void
    {
        if ($idRichiedente !== null && $idRichiedente <= 0) {
            throw new InvalidArgumentException('ID richiedente non valido.');
        }
        $this->idRichiedente = $idRichiedente;
    }

    public function getMotivo(): string { return $this->motivo; }
    public function setMotivo(string $motivo): void { $this->motivo = trim($motivo); }

    public function getDataRichiesta(): string { return $this->dataRichiesta; }
    public function setDataRichiesta(string $dataRichiesta): void { $this->dataRichiesta = trim($dataRichiesta); }

    public function getPenaleApplicata(): float { return $this->penaleApplicata; }
    public function setPenaleApplicata(float $penaleApplicata): void
    {
        if ($penaleApplicata < 0) {
            throw new InvalidArgumentException('Penale applicata non valida.');
        }
        $this->penaleApplicata = round($penaleApplicata, 2);
    }

    public function getImportoRimborsato(): float { return $this->importoRimborsato; }
    public function setImportoRimborsato(float $importoRimborsato): void
    {
        if ($importoRimborsato < 0) {
            throw new InvalidArgumentException('Importo rimborsato non valido.');
        }
        $this->importoRimborsato = round($importoRimborsato, 2);
    }

    public function getStato(): string { return $this->stato; }
    public function setStato(string $stato): void
    {
        $stato = strtolower(trim($stato));
        $ammessi = [self::STATO_RICHIESTA, self::STATO_ACCETTATA, self::STATO_RIFIUTATA, self::STATO_COMPLETATA];
        if (!in_array($stato, $ammessi, true)) {
            throw new InvalidArgumentException('Stato cancellazione non valido.');
        }
        $this->stato = $stato;
    }

    public function accetta(): void
    {
        if ($this->stato !== self::STATO_RICHIESTA) {
            throw new InvalidArgumentException('Accettazione cancellazione consentita solo da richiesta.');
        }
        $this->stato = self::STATO_ACCETTATA;
    }

    public function rifiuta(): void
    {
        if ($this->stato !== self::STATO_RICHIESTA) {
            throw new InvalidArgumentException('Rifiuto cancellazione consentito solo da richiesta.');
        }
        $this->stato = self::STATO_RIFIUTATA;
    }

    public function completa(): void
    {
        if ($this->stato !== self::STATO_ACCETTATA) {
            throw new InvalidArgumentException('Completamento cancellazione consentito solo da accettata.');
        }
        $this->stato = self::STATO_COMPLETATA;
    }
    public function isCompletata(): bool { return $this->stato === self::STATO_COMPLETATA; }

    public function toArray(): array
    {
        return [
            'idCancellazione' => $this->idCancellazione,
            'idPrenotazione' => $this->idPrenotazione,
            'tipoPrenotazione' => $this->tipoPrenotazione,
            'idRichiedente' => $this->idRichiedente,
            'motivo' => $this->motivo,
            'dataRichiesta' => $this->dataRichiesta,
            'penaleApplicata' => $this->penaleApplicata,
            'importoRimborsato' => $this->importoRimborsato,
            'stato' => $this->stato
        ];
    }

    public function __toString(): string
    {
        return 'Cancellazione #' . ($this->idCancellazione ?? 'nuova') . ' [' . $this->stato . ']';
    }
}
