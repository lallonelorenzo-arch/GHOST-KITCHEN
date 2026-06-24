<?php
declare(strict_types=1);

/**
 * Entity base per le prenotazioni.
 *
 * Rappresenta i dati comuni a ogni prenotazione: chi richiede il servizio,
 * data/ora, stato, importo e note. Le classi figlie aggiungono i campi
 * specifici per chef o ghost kitchen.
 */
abstract class EPrenotazione
{
    // Stati del ciclo di vita della prenotazione usati da Control e Foundation.
    // Usare costanti evita stringhe duplicate e riduce errori di battitura.
    public const STATO_IN_ATTESA = 'in_attesa';
    public const STATO_ACCETTATA = 'accettata';
    public const STATO_RIFIUTATA = 'rifiutata';
    public const STATO_CANCELLATA = 'cancellata';
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

    // I campi sono privati: l'oggetto protegge i propri dati tramite getter e setter.
    // Questo e un punto chiave dell'incapsulamento nelle Entity.

    // Il costruttore passa sempre dai setter, cosi le validazioni restano in un punto solo.
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
        // null e ammesso per oggetti non ancora salvati nel database.
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
        // Anche il richiedente puo essere null durante la costruzione iniziale dell'oggetto.
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
        // Whitelist degli stati: evita valori non previsti nel dominio.
        $statiAmmessi = [
            self::STATO_IN_ATTESA,
            self::STATO_ACCETTATA,
            self::STATO_RIFIUTATA,
            self::STATO_CANCELLATA,
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

        // Gli importi vengono normalizzati a due decimali per coerenza con valori monetari.
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
        // Transizione ammessa solo da in_attesa ad accettata.
        if ($this->stato !== self::STATO_IN_ATTESA) {
            throw new InvalidArgumentException('Transizione non valida: si puo accettare solo una prenotazione in_attesa.');
        }

        $this->stato = self::STATO_ACCETTATA;
    }

    public function rifiuta(): void
    {
        // Transizione ammessa solo da in_attesa a rifiutata.
        if ($this->stato !== self::STATO_IN_ATTESA) {
            throw new InvalidArgumentException('Transizione non valida: si puo rifiutare solo una prenotazione in_attesa.');
        }

        $this->stato = self::STATO_RIFIUTATA;
    }

    public function segnaComePagata(): void
    {
        // La prenotazione viene pagata solo dopo accettazione.
        if ($this->stato !== self::STATO_ACCETTATA) {
            throw new InvalidArgumentException('Transizione non valida: si puo segnare pagata solo una prenotazione accettata.');
        }

        $this->stato = self::STATO_PAGATA;
    }

    public function completa(): void
    {
        // Il completamento rappresenta un servizio gia pagato ed erogato.
        if ($this->stato !== self::STATO_PAGATA) {
            throw new InvalidArgumentException('Transizione non valida: si puo completare solo una prenotazione pagata.');
        }

        $this->stato = self::STATO_COMPLETATA;
    }

    public function isPagata(): bool
    {
        // Una prenotazione completata e considerata pagata perche ha gia superato quello stato.
        return in_array($this->stato, [self::STATO_PAGATA, self::STATO_COMPLETATA], true);
    }

    public function toArray(): array
    {
        // Utile per test/debug e per eventuali serializzazioni controllate.
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
