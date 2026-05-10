<?php
declare(strict_types=1);

/**
 * Entity GhostKitchen.
 */
class EGhostKitchen
{
    public const STATO_ATTIVA = 'attiva';
    public const STATO_SOSPESA = 'sospesa';
    public const STATO_NON_DISPONIBILE = 'non_disponibile';

    private ?int $id;
    private ?int $idGestore;
    private string $nome;
    private string $descrizione;
    private string $indirizzo;
    private string $citta;
    private string $cap;
    private float $prezzoOrario;
    private int $capienza;
    private float $mq;
    private string $stato;
    private float $valutazioneMedia;
    private int $numeroRecensioni;

    public function __construct(
        ?int $id = null,
        ?int $idGestore = null,
        string $nome = '',
        string $descrizione = '',
        string $indirizzo = '',
        string $citta = '',
        string $cap = '',
        float $prezzoOrario = 0.0,
        int $capienza = 0,
        float $mq = 0.0,
        string $stato = self::STATO_ATTIVA,
        float $valutazioneMedia = 0.0,
        int $numeroRecensioni = 0
    ) {
        $this->setId($id);
        $this->setIdGestore($idGestore);
        $this->setNome($nome);
        $this->setDescrizione($descrizione);
        $this->setIndirizzo($indirizzo);
        $this->setCitta($citta);
        $this->setCap($cap);
        $this->setPrezzoOrario($prezzoOrario);
        $this->setCapienza($capienza);
        $this->setMq($mq);
        $this->setStato($stato);
        $this->setValutazioneMedia($valutazioneMedia);
        $this->setNumeroRecensioni($numeroRecensioni);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        if ($id !== null && $id <= 0) {
            throw new InvalidArgumentException('ID ghost kitchen non valido.');
        }

        $this->id = $id;
    }

    public function getIdGestore(): ?int
    {
        return $this->idGestore;
    }

    public function setIdGestore(?int $idGestore): void
    {
        if ($idGestore !== null && $idGestore <= 0) {
            throw new InvalidArgumentException('ID gestore non valido.');
        }

        $this->idGestore = $idGestore;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function setNome(string $nome): void
    {
        $this->nome = trim($nome);
    }

    public function getDescrizione(): string
    {
        return $this->descrizione;
    }

    public function setDescrizione(string $descrizione): void
    {
        $this->descrizione = trim($descrizione);
    }

    public function getIndirizzo(): string
    {
        return $this->indirizzo;
    }

    public function setIndirizzo(string $indirizzo): void
    {
        $this->indirizzo = trim($indirizzo);
    }

    public function getCitta(): string
    {
        return $this->citta;
    }

    public function setCitta(string $citta): void
    {
        $this->citta = trim($citta);
    }

    public function getCap(): string
    {
        return $this->cap;
    }

    public function setCap(string $cap): void
    {
        $this->cap = trim($cap);
    }

    public function getPrezzoOrario(): float
    {
        return $this->prezzoOrario;
    }

    public function setPrezzoOrario(float $prezzoOrario): void
    {
        if ($prezzoOrario < 0) {
            throw new InvalidArgumentException('Prezzo orario non valido.');
        }

        $this->prezzoOrario = round($prezzoOrario, 2);
    }

    public function getCapienza(): int
    {
        return $this->capienza;
    }

    public function setCapienza(int $capienza): void
    {
        if ($capienza < 0) {
            throw new InvalidArgumentException('Capienza non valida.');
        }

        $this->capienza = $capienza;
    }

    public function getMq(): float
    {
        return $this->mq;
    }

    public function setMq(float $mq): void
    {
        if ($mq < 0) {
            throw new InvalidArgumentException('Metri quadri non validi.');
        }

        $this->mq = round($mq, 2);
    }

    public function getStato(): string
    {
        return $this->stato;
    }

    public function setStato(string $stato): void
    {
        $stato = strtolower(trim($stato));
        $statiAmmessi = [self::STATO_ATTIVA, self::STATO_SOSPESA, self::STATO_NON_DISPONIBILE];

        if (!in_array($stato, $statiAmmessi, true)) {
            throw new InvalidArgumentException('Stato ghost kitchen non valido.');
        }

        $this->stato = $stato;
    }

    public function getValutazioneMedia(): float
    {
        return $this->valutazioneMedia;
    }

    public function setValutazioneMedia(float $valutazioneMedia): void
    {
        if ($valutazioneMedia < 0 || $valutazioneMedia > 5) {
            throw new InvalidArgumentException('Valutazione media non valida.');
        }

        $this->valutazioneMedia = round($valutazioneMedia, 2);
    }

    public function getNumeroRecensioni(): int
    {
        return $this->numeroRecensioni;
    }

    public function setNumeroRecensioni(int $numeroRecensioni): void
    {
        if ($numeroRecensioni < 0) {
            throw new InvalidArgumentException('Numero recensioni non valido.');
        }

        $this->numeroRecensioni = $numeroRecensioni;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'idGestore' => $this->idGestore,
            'nome' => $this->nome,
            'descrizione' => $this->descrizione,
            'indirizzo' => $this->indirizzo,
            'citta' => $this->citta,
            'cap' => $this->cap,
            'prezzoOrario' => $this->prezzoOrario,
            'capienza' => $this->capienza,
            'mq' => $this->mq,
            'stato' => $this->stato,
            'valutazioneMedia' => $this->valutazioneMedia,
            'numeroRecensioni' => $this->numeroRecensioni
        ];
    }

    public function __toString(): string
    {
        return 'GhostKitchen #' . ($this->id ?? 'nuova') .
            ' ' . $this->nome .
            ' - ' . $this->citta .
            ' - €/h ' . $this->prezzoOrario .
            ' [' . $this->stato . ']';
    }
}
