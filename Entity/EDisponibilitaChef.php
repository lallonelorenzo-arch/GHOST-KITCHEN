<?php

declare(strict_types=1);

/**
 * Entity DisponibilitaChef.
 *
 * Rappresenta uno slot temporale in cui uno chef è disponibile
 * per una prenotazione a domicilio.
 */
class EDisponibilitaChef
{
    public const FASCIA_PRANZO = 'pranzo';
    public const FASCIA_CENA = 'cena';
    public const ORA_PRANZO_INIZIO = '12:00';
    public const ORA_PRANZO_FINE = '15:00';
    public const ORA_CENA_INIZIO = '19:00';
    public const ORA_CENA_FINE = '23:00';

    public const STATO_LIBERA = 'libera';
    public const STATO_OCCUPATA = 'occupata';
    public const STATO_BLOCCATA = 'bloccata';

    private ?int $idDisponibilitaChef;
    private ?int $idChef;
    private string $data;
    private string $oraInizio;
    private string $oraFine;
    private string $stato;

    public function __construct(
        ?int $idDisponibilitaChef = null,
        ?int $idChef = null,
        string $data = '',
        string $oraInizio = '',
        string $oraFine = '',
        string $stato = self::STATO_LIBERA
    ) {
        $this->setIdDisponibilitaChef($idDisponibilitaChef);
        $this->setIdChef($idChef);
        $this->setData($data);
        $this->setOraInizio($oraInizio);
        $this->setOraFine($oraFine);
        $this->setStato($stato);
    }

    public function getIdDisponibilitaChef(): ?int
    {
        return $this->idDisponibilitaChef;
    }

    public function setIdDisponibilitaChef(?int $idDisponibilitaChef): void
    {
        if ($idDisponibilitaChef !== null && $idDisponibilitaChef <= 0) {
            throw new InvalidArgumentException('ID disponibilità chef non valido.');
        }

        $this->idDisponibilitaChef = $idDisponibilitaChef;
    }

    public function getIdChef(): ?int
    {
        return $this->idChef;
    }

    public function setIdChef(?int $idChef): void
    {
        if ($idChef !== null && $idChef <= 0) {
            throw new InvalidArgumentException('ID chef non valido.');
        }

        $this->idChef = $idChef;
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
            throw new InvalidArgumentException('Stato disponibilità chef non valido.');
        }

        $this->stato = $stato;
    }

    public function isLibera(): bool
    {
        return $this->stato === self::STATO_LIBERA;
    }

    public function isOccupata(): bool
    {
        return $this->stato === self::STATO_OCCUPATA;
    }

    public function isBloccata(): bool
    {
        return $this->stato === self::STATO_BLOCCATA;
    }

    public function getFasciaServizio(): string
    {
        return self::fasciaDaOra($this->oraInizio);
    }

    public static function orariPerFascia(string $fascia): array
    {
        return match (strtolower(trim($fascia))) {
            self::FASCIA_PRANZO => [self::ORA_PRANZO_INIZIO, self::ORA_PRANZO_FINE],
            self::FASCIA_CENA => [self::ORA_CENA_INIZIO, self::ORA_CENA_FINE],
            default => throw new InvalidArgumentException('Fascia servizio non valida.'),
        };
    }

    public static function fasciaDaOra(string $oraInizio): string
    {
        return substr(trim($oraInizio), 0, 5) < '16:00'
            ? self::FASCIA_PRANZO
            : self::FASCIA_CENA;
    }

    public function occupa(): void
    {
        if ($this->stato !== self::STATO_LIBERA) {
            throw new InvalidArgumentException('Solo una disponibilità libera può essere occupata.');
        }

        $this->stato = self::STATO_OCCUPATA;
    }

    public function libera(): void
    {
        $this->stato = self::STATO_LIBERA;
    }

    public function blocca(): void
    {
        if ($this->stato === self::STATO_OCCUPATA) {
            throw new InvalidArgumentException('Una disponibilità occupata non può essere bloccata.');
        }

        $this->stato = self::STATO_BLOCCATA;
    }

    public function validaPerPrenotazione(): void
    {
        if ($this->idChef === null) {
            throw new InvalidArgumentException('Chef obbligatorio.');
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
            throw new InvalidArgumentException('La disponibilità dello chef non è libera.');
        }
    }

    public function toArray(): array
    {
        return [
            'idDisponibilitaChef' => $this->idDisponibilitaChef,
            'idChef' => $this->idChef,
            'data' => $this->data,
            'oraInizio' => $this->oraInizio,
            'oraFine' => $this->oraFine,
            'stato' => $this->stato
        ];
    }

    public function __toString(): string
    {
        return 'Disponibilità chef #' . ($this->idDisponibilitaChef ?? 'nuova') .
            ' - Chef #' . ($this->idChef ?? 'n/d') .
            ' - ' . $this->data .
            ' ' . $this->oraInizio . '-' . $this->oraFine .
            ' [' . $this->stato . ']';
    }
}

?>
