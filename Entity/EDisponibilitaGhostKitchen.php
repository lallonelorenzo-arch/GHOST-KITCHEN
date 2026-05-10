<?php
declare(strict_types=1);

class EDisponibilitaGhostKitchen
{
    public const STATO_LIBERA = 'libera';
    public const STATO_OCCUPATA = 'occupata';
    public const STATO_BLOCCATA = 'bloccata';

    private ?int $idDisponibilitaGhostKitchen;
    private ?int $idGhostKitchen;
    private string $data;
    private string $oraInizio;
    private string $oraFine;
    private string $stato;

    public function __construct(
        ?int $idDisponibilitaGhostKitchen = null,
        ?int $idGhostKitchen = null,
        string $data = '',
        string $oraInizio = '',
        string $oraFine = '',
        string $stato = self::STATO_LIBERA
    ) {
        $this->setIdDisponibilitaGhostKitchen($idDisponibilitaGhostKitchen);
        $this->setIdGhostKitchen($idGhostKitchen);
        $this->setData($data);
        $this->setOraInizio($oraInizio);
        $this->setOraFine($oraFine);
        $this->setStato($stato);
    }

    public function getIdDisponibilitaGhostKitchen(): ?int
    {
        return $this->idDisponibilitaGhostKitchen;
    }

    public function setIdDisponibilitaGhostKitchen(?int $idDisponibilitaGhostKitchen): void
    {
        if ($idDisponibilitaGhostKitchen !== null && $idDisponibilitaGhostKitchen <= 0) {
            throw new InvalidArgumentException('ID disponibilita ghost kitchen non valido.');
        }

        $this->idDisponibilitaGhostKitchen = $idDisponibilitaGhostKitchen;
    }

    public function getIdGhostKitchen(): ?int
    {
        return $this->idGhostKitchen;
    }

    public function setIdGhostKitchen(?int $idGhostKitchen): void
    {
        if ($idGhostKitchen !== null && $idGhostKitchen <= 0) {
            throw new InvalidArgumentException('ID ghost kitchen non valido.');
        }

        $this->idGhostKitchen = $idGhostKitchen;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function setData(string $data): void
    {
        $this->data = trim($data);
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

        $statiConsentiti = [
            self::STATO_LIBERA,
            self::STATO_OCCUPATA,
            self::STATO_BLOCCATA
        ];

        if (!in_array($stato, $statiConsentiti, true)) {
            throw new InvalidArgumentException('Stato disponibilita ghost kitchen non valido.');
        }

        $this->stato = $stato;
    }

    public function isLibera(): bool
    {
        return $this->stato === self::STATO_LIBERA;
    }

    public function occupa(): void
    {
        if ($this->stato !== self::STATO_LIBERA) {
            throw new InvalidArgumentException('Solo una disponibilita libera puo essere occupata.');
        }

        $this->stato = self::STATO_OCCUPATA;
    }

    public function libera(): void
    {
        $this->stato = self::STATO_LIBERA;
    }

    public function blocca(): void
    {
        $this->stato = self::STATO_BLOCCATA;
    }

    public function validaPerPrenotazione(): void
    {
        if ($this->idGhostKitchen === null) {
            throw new InvalidArgumentException('Ghost kitchen obbligatoria.');
        }

        if ($this->data === '') {
            throw new InvalidArgumentException('Data obbligatoria.');
        }

        if ($this->oraInizio === '') {
            throw new InvalidArgumentException('Ora inizio obbligatoria.');
        }

        if ($this->oraFine === '') {
            throw new InvalidArgumentException('Ora fine obbligatoria.');
        }

        if (!$this->isLibera()) {
            throw new InvalidArgumentException('La disponibilita non e libera.');
        }
    }

    public function toArray(): array
    {
        return [
            'idDisponibilitaGhostKitchen' => $this->idDisponibilitaGhostKitchen,
            'idGhostKitchen' => $this->idGhostKitchen,
            'data' => $this->data,
            'oraInizio' => $this->oraInizio,
            'oraFine' => $this->oraFine,
            'stato' => $this->stato
        ];
    }

    public function __toString(): string
    {
        return 'Disponibilita ghost kitchen #' . $this->idDisponibilitaGhostKitchen .
            ' - Ghost kitchen #' . $this->idGhostKitchen .
            ' - ' . $this->data .
            ' ' . $this->oraInizio . '-' . $this->oraFine .
            ' [' . $this->stato . ']';
    }
}
