<?php
declare(strict_types=1);

class ESegnalazione
{
    public const TARGET_UTENTE = 'utente';
    public const TARGET_CHEF = 'chef';
    public const TARGET_GHOST_KITCHEN = 'ghost_kitchen';
    public const TARGET_RECENSIONE = 'recensione';
    public const TARGET_MENU = 'menu';

    public const STATO_APERTA = 'aperta';
    public const STATO_IN_VALUTAZIONE = 'in_valutazione';
    public const STATO_RISOLTA = 'risolta';
    public const STATO_ARCHIVIATA = 'archiviata';
    public const STATO_RESPINTA = 'respinta';

    private ?int $idSegnalazione;
    private ?int $idSegnalante;
    private string $tipoTarget;
    private ?int $idTarget;
    private string $motivo;
    private string $descrizione;
    private string $stato;
    private string $dataSegnalazione;
    private string $dataGestione;
    private string $noteAdmin;

    public function __construct(
        ?int $idSegnalazione = null,
        ?int $idSegnalante = null,
        string $tipoTarget = self::TARGET_UTENTE,
        ?int $idTarget = null,
        string $motivo = '',
        string $descrizione = '',
        string $stato = self::STATO_APERTA,
        string $dataSegnalazione = '',
        string $dataGestione = '',
        string $noteAdmin = ''
    ) {
        $this->setIdSegnalazione($idSegnalazione);
        $this->setIdSegnalante($idSegnalante);
        $this->setTipoTarget($tipoTarget);
        $this->setIdTarget($idTarget);
        $this->setMotivo($motivo);
        $this->setDescrizione($descrizione);
        $this->setStato($stato);
        $this->setDataSegnalazione($dataSegnalazione);
        $this->setDataGestione($dataGestione);
        $this->setNoteAdmin($noteAdmin);
    }

    public function getIdSegnalazione(): ?int { return $this->idSegnalazione; }
    public function setIdSegnalazione(?int $idSegnalazione): void
    {
        if ($idSegnalazione !== null && $idSegnalazione <= 0) {
            throw new InvalidArgumentException('ID segnalazione non valido.');
        }
        $this->idSegnalazione = $idSegnalazione;
    }

    public function getIdSegnalante(): ?int { return $this->idSegnalante; }
    public function setIdSegnalante(?int $idSegnalante): void
    {
        if ($idSegnalante !== null && $idSegnalante <= 0) {
            throw new InvalidArgumentException('ID segnalante non valido.');
        }
        $this->idSegnalante = $idSegnalante;
    }

    public function getTipoTarget(): string { return $this->tipoTarget; }
    public function setTipoTarget(string $tipoTarget): void
    {
        $tipoTarget = strtolower(trim($tipoTarget));
        $ammessi = [
            self::TARGET_UTENTE,
            self::TARGET_CHEF,
            self::TARGET_GHOST_KITCHEN,
            self::TARGET_RECENSIONE,
            self::TARGET_MENU
        ];
        if (!in_array($tipoTarget, $ammessi, true)) {
            throw new InvalidArgumentException('Tipo target segnalazione non valido.');
        }
        $this->tipoTarget = $tipoTarget;
    }

    public function getIdTarget(): ?int { return $this->idTarget; }
    public function setIdTarget(?int $idTarget): void
    {
        if ($idTarget !== null && $idTarget <= 0) {
            throw new InvalidArgumentException('ID target segnalazione non valido.');
        }
        $this->idTarget = $idTarget;
    }

    public function getMotivo(): string { return $this->motivo; }
    public function setMotivo(string $motivo): void { $this->motivo = trim($motivo); }

    public function getDescrizione(): string { return $this->descrizione; }
    public function setDescrizione(string $descrizione): void { $this->descrizione = trim($descrizione); }

    public function getStato(): string { return $this->stato; }
    public function setStato(string $stato): void
    {
        $stato = strtolower(trim($stato));
        $ammessi = [
            self::STATO_APERTA,
            self::STATO_IN_VALUTAZIONE,
            self::STATO_RISOLTA,
            self::STATO_ARCHIVIATA,
            self::STATO_RESPINTA
        ];
        if (!in_array($stato, $ammessi, true)) {
            throw new InvalidArgumentException('Stato segnalazione non valido.');
        }
        $this->stato = $stato;
    }

    public function getDataSegnalazione(): string { return $this->dataSegnalazione; }
    public function setDataSegnalazione(string $dataSegnalazione): void { $this->dataSegnalazione = trim($dataSegnalazione); }

    public function getDataGestione(): string { return $this->dataGestione; }
    public function setDataGestione(string $dataGestione): void { $this->dataGestione = trim($dataGestione); }

    public function getNoteAdmin(): string { return $this->noteAdmin; }
    public function setNoteAdmin(string $noteAdmin): void { $this->noteAdmin = trim($noteAdmin); }

    public function prendiInCarico(): void
    {
        if ($this->stato !== self::STATO_APERTA) {
            throw new InvalidArgumentException('Presa in carico consentita solo da aperta.');
        }
        $this->stato = self::STATO_IN_VALUTAZIONE;
    }

    public function risolvi(): void
    {
        if ($this->stato !== self::STATO_IN_VALUTAZIONE) {
            throw new InvalidArgumentException('Risoluzione consentita solo da in_valutazione.');
        }
        $this->stato = self::STATO_RISOLTA;
    }

    public function archivia(): void
    {
        if (!in_array($this->stato, [self::STATO_RISOLTA, self::STATO_RESPINTA], true)) {
            throw new InvalidArgumentException('Archiviazione consentita solo da risolta o respinta.');
        }
        $this->stato = self::STATO_ARCHIVIATA;
    }

    public function respingi(): void
    {
        if (!in_array($this->stato, [self::STATO_APERTA, self::STATO_IN_VALUTAZIONE], true)) {
            throw new InvalidArgumentException('Respingimento consentito solo da aperta o in_valutazione.');
        }
        $this->stato = self::STATO_RESPINTA;
    }
    public function isAperta(): bool { return $this->stato === self::STATO_APERTA; }
    public function isRisolta(): bool { return $this->stato === self::STATO_RISOLTA; }

    public function toArray(): array
    {
        return [
            'idSegnalazione' => $this->idSegnalazione,
            'idSegnalante' => $this->idSegnalante,
            'tipoTarget' => $this->tipoTarget,
            'idTarget' => $this->idTarget,
            'motivo' => $this->motivo,
            'descrizione' => $this->descrizione,
            'stato' => $this->stato,
            'dataSegnalazione' => $this->dataSegnalazione,
            'dataGestione' => $this->dataGestione,
            'noteAdmin' => $this->noteAdmin
        ];
    }

    public function __toString(): string
    {
        return 'Segnalazione #' . ($this->idSegnalazione ?? 'nuova') . ' [' . $this->stato . ']';
    }
}
