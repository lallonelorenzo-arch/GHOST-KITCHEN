<?php
declare(strict_types=1);

/**
 * Entity base per le prenotazioni.
 */
abstract class EPrenotazione
{
    public const STATO_IN_ATTESA = 'in_attesa';
    public const STATO_ACCETTATA = 'accettata';
    public const STATO_RIFIUTATA = 'rifiutata';
    public const STATO_PAGATA = 'pagata';
    public const STATO_COMPLETATA = 'completata';

    private ?int $idPrenotazione;
    private ?int $idRichiedente;
    private string $dataCreazione;
    private string $dataServizio;
    private string $oraInizio;
    private string $oraFine;
    private string $stato;
    private float $importoTotale;
    private string $note;

    public function __construct(
        ?int $idPrenotazione = null,
        ?int $idRichiedente = null,
        string $dataCreazione = '',
        string $dataServizio = '',
        string $oraInizio = '',
        string $oraFine = '',
        string $stato = self::STATO_IN_ATTESA,
        float $importoTotale = 0.0,
        string $note = ''
    ) {
        $this->setIdPrenotazione($idPrenotazione);
        $this->setIdRichiedente($idRichiedente);
        $this->setDataCreazione($dataCreazione);
        $this->setDataServizio($dataServizio);
        $this->setOraInizio($oraInizio);
        $this->setOraFine($oraFine);
        $this->setStato($stato);
        $this->setImportoTotale($importoTotale);
        $this->setNote($note);
    }

    public function getIdPrenotazione(): ?int
    {
        return $this->idPrenotazione;
    }

    public function setIdPrenotazione(?int $idPrenotazione): void
    {
        if ($idPrenotazione !== null && $idPrenotazione <= 0) {
            throw new InvalidArgumentException('ID prenotazione non valido.');
        }

        $this->idPrenotazione = $idPrenotazione;
    }

    public function getIdRichiedente(): ?int
    {
        return $this->idRichiedente;
    }

    public function setIdRichiedente(?int $idRichiedente): void
    {
        if ($idRichiedente !== null && $idRichiedente <= 0) {
            throw new InvalidArgumentException('ID richiedente non valido.');
        }

        $this->idRichiedente = $idRichiedente;
    }

    public function getDataCreazione(): string
    {
        return $this->dataCreazione;
    }

    public function setDataCreazione(string $dataCreazione): void
    {
        $this->dataCreazione = trim($dataCreazione);
    }

    public function getDataServizio(): string
    {
        return $this->dataServizio;
    }

    public function setDataServizio(string $dataServizio): void
    {
        $this->dataServizio = trim($dataServizio);
    }

    public function getOraInizio(): string
    {
        return $this->oraInizio;
    }

    public function setOraInizio(string $oraInizio): void
    {
        $this->oraInizio = trim($oraInizio);
    }

    public function getOraFine(): string
    {
        return $this->oraFine;
    }

    public function setOraFine(string $oraFine): void
    {
        $this->oraFine = trim($oraFine);
    }

    public function getStato(): string
    {
        return $this->stato;
    }

    public function setStato(string $stato): void
    {
        $stato = strtolower(trim($stato));
        $statiAmmessi = [
            self::STATO_IN_ATTESA,
            self::STATO_ACCETTATA,
            self::STATO_RIFIUTATA,
            self::STATO_PAGATA,
            self::STATO_COMPLETATA
        ];

        if (!in_array($stato, $statiAmmessi, true)) {
            throw new InvalidArgumentException('Stato prenotazione non valido.');
        }

        $this->stato = $stato;
    }

    public function getImportoTotale(): float
    {
        return $this->importoTotale;
    }

    public function setImportoTotale(float $importoTotale): void
    {
        if ($importoTotale < 0) {
            throw new InvalidArgumentException('Importo totale non valido.');
        }

        $this->importoTotale = round($importoTotale, 2);
    }

    public function getNote(): string
    {
        return $this->note;
    }

    public function setNote(string $note): void
    {
        $this->note = trim($note);
    }

    public function accetta(): void
    {
        if ($this->stato !== self::STATO_IN_ATTESA) {
            throw new InvalidArgumentException('Transizione non valida: si puo accettare solo una prenotazione in_attesa.');
        }

        $this->stato = self::STATO_ACCETTATA;
    }

    public function rifiuta(): void
    {
        if ($this->stato !== self::STATO_IN_ATTESA) {
            throw new InvalidArgumentException('Transizione non valida: si puo rifiutare solo una prenotazione in_attesa.');
        }

        $this->stato = self::STATO_RIFIUTATA;
    }

    public function segnaComePagata(): void
    {
        if ($this->stato !== self::STATO_ACCETTATA) {
            throw new InvalidArgumentException('Transizione non valida: si puo segnare pagata solo una prenotazione accettata.');
        }

        $this->stato = self::STATO_PAGATA;
    }

    public function completa(): void
    {
        if ($this->stato !== self::STATO_PAGATA) {
            throw new InvalidArgumentException('Transizione non valida: si puo completare solo una prenotazione pagata.');
        }

        $this->stato = self::STATO_COMPLETATA;
    }

    public function isPagata(): bool
    {
        return in_array($this->stato, [self::STATO_PAGATA, self::STATO_COMPLETATA], true);
    }

    public function toArray(): array
    {
        return [
            'idPrenotazione' => $this->idPrenotazione,
            'idRichiedente' => $this->idRichiedente,
            'dataCreazione' => $this->dataCreazione,
            'dataServizio' => $this->dataServizio,
            'oraInizio' => $this->oraInizio,
            'oraFine' => $this->oraFine,
            'stato' => $this->stato,
            'importoTotale' => $this->importoTotale,
            'note' => $this->note
        ];
    }

    public function __toString(): string
    {
        return 'Prenotazione #' . ($this->idPrenotazione ?? 'nuova') . ' richiedente=' . ($this->idRichiedente ?? 'n/d') . ' [' . $this->stato . ']';
    }
}
