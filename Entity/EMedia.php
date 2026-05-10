<?php
declare(strict_types=1);

class EMedia
{
    public const OWNER_CHEF = 'chef';
    public const OWNER_MENU = 'menu';
    public const OWNER_GHOST_KITCHEN = 'ghost_kitchen';
    public const OWNER_PIATTO = 'piatto';

    public const TIPO_MEDIA_FOTO_PROFILO = 'foto_profilo';
    public const TIPO_MEDIA_FOTO_MENU = 'foto_menu';
    public const TIPO_MEDIA_FOTO_PIATTO = 'foto_piatto';
    public const TIPO_MEDIA_FOTO_AMBIENTE = 'foto_ambiente';
    public const TIPO_MEDIA_PLANIMETRIA = 'planimetria';
    public const TIPO_MEDIA_GENERICA = 'generica';

    public const STATO_ATTIVO = 'attivo';
    public const STATO_NASCOSTO = 'nascosto';
    public const STATO_RIMOSSO = 'rimosso';

    private ?int $idMedia;
    private string $tipoOwner;
    private ?int $idOwner;
    private string $tipoMedia;
    private string $nomeFile;
    private string $pathFile;
    private string $mimeType;
    private string $descrizione;
    private string $dataCaricamento;
    private int $ordine;
    private string $stato;

    public function __construct(
        ?int $idMedia = null,
        string $tipoOwner = self::OWNER_CHEF,
        ?int $idOwner = null,
        string $tipoMedia = self::TIPO_MEDIA_GENERICA,
        string $nomeFile = '',
        string $pathFile = '',
        string $mimeType = '',
        string $descrizione = '',
        string $dataCaricamento = '',
        int $ordine = 0,
        string $stato = self::STATO_ATTIVO
    ) {
        $this->setIdMedia($idMedia);
        $this->setTipoOwner($tipoOwner);
        $this->setIdOwner($idOwner);
        $this->setTipoMedia($tipoMedia);
        $this->setNomeFile($nomeFile);
        $this->setPathFile($pathFile);
        $this->setMimeType($mimeType);
        $this->setDescrizione($descrizione);
        $this->setDataCaricamento($dataCaricamento);
        $this->setOrdine($ordine);
        $this->setStato($stato);
    }

    public function getIdMedia(): ?int
    {
        return $this->idMedia;
    }

    public function setIdMedia(?int $idMedia): void
    {
        if ($idMedia !== null && $idMedia <= 0) {
            throw new InvalidArgumentException('ID media non valido.');
        }

        $this->idMedia = $idMedia;
    }

    public function getTipoOwner(): string
    {
        return $this->tipoOwner;
    }

    public function setTipoOwner(string $tipoOwner): void
    {
        $tipoOwner = strtolower(trim($tipoOwner));
        $tipiAmmessi = [self::OWNER_CHEF, self::OWNER_MENU, self::OWNER_GHOST_KITCHEN, self::OWNER_PIATTO];

        if (!in_array($tipoOwner, $tipiAmmessi, true)) {
            throw new InvalidArgumentException('Tipo owner media non valido.');
        }

        $this->tipoOwner = $tipoOwner;
    }

    public function getIdOwner(): ?int
    {
        return $this->idOwner;
    }

    public function setIdOwner(?int $idOwner): void
    {
        if ($idOwner !== null && $idOwner <= 0) {
            throw new InvalidArgumentException('ID owner media non valido.');
        }

        $this->idOwner = $idOwner;
    }

    public function getTipoMedia(): string
    {
        return $this->tipoMedia;
    }

    public function setTipoMedia(string $tipoMedia): void
    {
        $tipoMedia = strtolower(trim($tipoMedia));
        $tipiAmmessi = [
            self::TIPO_MEDIA_FOTO_PROFILO,
            self::TIPO_MEDIA_FOTO_MENU,
            self::TIPO_MEDIA_FOTO_PIATTO,
            self::TIPO_MEDIA_FOTO_AMBIENTE,
            self::TIPO_MEDIA_PLANIMETRIA,
            self::TIPO_MEDIA_GENERICA
        ];

        if (!in_array($tipoMedia, $tipiAmmessi, true)) {
            throw new InvalidArgumentException('Tipo media non valido.');
        }

        $this->tipoMedia = $tipoMedia;
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

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): void
    {
        $this->mimeType = trim($mimeType);
    }

    public function getDescrizione(): string
    {
        return $this->descrizione;
    }

    public function setDescrizione(string $descrizione): void
    {
        $this->descrizione = trim($descrizione);
    }

    public function getDataCaricamento(): string
    {
        return $this->dataCaricamento;
    }

    public function setDataCaricamento(string $dataCaricamento): void
    {
        $this->dataCaricamento = trim($dataCaricamento);
    }

    public function getOrdine(): int
    {
        return $this->ordine;
    }

    public function setOrdine(int $ordine): void
    {
        if ($ordine < 0) {
            throw new InvalidArgumentException('Ordine media non valido.');
        }

        $this->ordine = $ordine;
    }

    public function getStato(): string
    {
        return $this->stato;
    }

    public function setStato(string $stato): void
    {
        $stato = strtolower(trim($stato));
        $statiAmmessi = [self::STATO_ATTIVO, self::STATO_NASCOSTO, self::STATO_RIMOSSO];

        if (!in_array($stato, $statiAmmessi, true)) {
            throw new InvalidArgumentException('Stato media non valido.');
        }

        $this->stato = $stato;
    }

    public function toArray(): array
    {
        return [
            'idMedia' => $this->idMedia,
            'tipoOwner' => $this->tipoOwner,
            'idOwner' => $this->idOwner,
            'tipoMedia' => $this->tipoMedia,
            'nomeFile' => $this->nomeFile,
            'pathFile' => $this->pathFile,
            'mimeType' => $this->mimeType,
            'descrizione' => $this->descrizione,
            'dataCaricamento' => $this->dataCaricamento,
            'ordine' => $this->ordine,
            'stato' => $this->stato
        ];
    }

    public function __toString(): string
    {
        return 'Media #' . ($this->idMedia ?? 'nuova') .
            ' - ' . $this->tipoOwner .
            ' #' . ($this->idOwner ?? 'n/d') .
            ' [' . $this->tipoMedia . ']';
    }
}
