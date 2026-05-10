<?php
declare(strict_types=1);

class ECertificazione
{
    public const STATO_IN_ATTESA = 'in_attesa';
    public const STATO_APPROVATA = 'approvata';
    public const STATO_RIFIUTATA = 'rifiutata';

    private ?int $idCertificazione;
    private ?int $idChef;
    private string $tipo;
    private string $nomeFile;
    private string $pathFile;
    private string $stato;
    private string $dataCaricamento;
    private string $dataValidazione;
    private string $noteAdmin;

    public function __construct(
        ?int $idCertificazione = null,
        ?int $idChef = null,
        string $tipo = '',
        string $nomeFile = '',
        string $pathFile = '',
        string $stato = self::STATO_IN_ATTESA,
        string $dataCaricamento = '',
        string $dataValidazione = '',
        string $noteAdmin = ''
    ) {
        $this->setIdCertificazione($idCertificazione);
        $this->setIdChef($idChef);
        $this->setTipo($tipo);
        $this->setNomeFile($nomeFile);
        $this->setPathFile($pathFile);
        $this->setStato($stato);
        $this->setDataCaricamento($dataCaricamento);
        $this->setDataValidazione($dataValidazione);
        $this->setNoteAdmin($noteAdmin);
    }

    public function getIdCertificazione(): ?int
    {
        return $this->idCertificazione;
    }

    public function setIdCertificazione(?int $idCertificazione): void
    {
        if ($idCertificazione !== null && $idCertificazione <= 0) {
            throw new InvalidArgumentException('ID certificazione non valido.');
        }

        $this->idCertificazione = $idCertificazione;
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

    public function getTipo(): string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): void
    {
        $this->tipo = trim($tipo);
    }

    public function getNomeFile(): string
    {
        return $this->nomeFile;
    }

    public function setNomeFile(string $nomeFile): void
    {
        $this->nomeFile = trim($nomeFile);
    }

    public function getPathFile(): string
    {
        return $this->pathFile;
    }

    public function setPathFile(string $pathFile): void
    {
        $this->pathFile = trim($pathFile);
    }

    public function getStato(): string
    {
        return $this->stato;
    }

    public function setStato(string $stato): void
    {
        $stato = strtolower(trim($stato));
        $statiAmmessi = [self::STATO_IN_ATTESA, self::STATO_APPROVATA, self::STATO_RIFIUTATA];

        if (!in_array($stato, $statiAmmessi, true)) {
            throw new InvalidArgumentException('Stato certificazione non valido.');
        }

        $this->stato = $stato;
    }

    public function getDataCaricamento(): string
    {
        return $this->dataCaricamento;
    }

    public function setDataCaricamento(string $dataCaricamento): void
    {
        $this->dataCaricamento = trim($dataCaricamento);
    }

    public function getDataValidazione(): string
    {
        return $this->dataValidazione;
    }

    public function setDataValidazione(string $dataValidazione): void
    {
        $this->dataValidazione = trim($dataValidazione);
    }

    public function getNoteAdmin(): string
    {
        return $this->noteAdmin;
    }

    public function setNoteAdmin(string $noteAdmin): void
    {
        $this->noteAdmin = trim($noteAdmin);
    }

    public function toArray(): array
    {
        return [
            'idCertificazione' => $this->idCertificazione,
            'idChef' => $this->idChef,
            'tipo' => $this->tipo,
            'nomeFile' => $this->nomeFile,
            'pathFile' => $this->pathFile,
            'stato' => $this->stato,
            'dataCaricamento' => $this->dataCaricamento,
            'dataValidazione' => $this->dataValidazione,
            'noteAdmin' => $this->noteAdmin
        ];
    }
}
