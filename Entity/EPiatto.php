<?php
declare(strict_types=1);

class EPiatto
{
    public const CATEGORIA_ANTIPASTO = 'antipasto';
    public const CATEGORIA_PRIMO = 'primo';
    public const CATEGORIA_SECONDO = 'secondo';
    public const CATEGORIA_CONTORNO = 'contorno';
    public const CATEGORIA_DOLCE = 'dolce';
    public const CATEGORIA_BEVANDA = 'bevanda';
    public const CATEGORIA_ALTRO = 'altro';

    private ?int $idPiatto;
    private ?int $idMenu;
    private string $nome;
    private string $categoria;
    private string $descrizione;
    private string $ingredienti;
    private string $allergeni;
    private float $prezzoSupplemento;
    private int $ordineVisualizzazione;

    public function __construct(
        ?int $idPiatto = null,
        ?int $idMenu = null,
        string $nome = '',
        string $categoria = self::CATEGORIA_ALTRO,
        string $descrizione = '',
        string $ingredienti = '',
        string $allergeni = '',
        float $prezzoSupplemento = 0.0,
        int $ordineVisualizzazione = 0
    ) {
        $this->setIdPiatto($idPiatto);
        $this->setIdMenu($idMenu);
        $this->setNome($nome);
        $this->setCategoria($categoria);
        $this->setDescrizione($descrizione);
        $this->setIngredienti($ingredienti);
        $this->setAllergeni($allergeni);
        $this->setPrezzoSupplemento($prezzoSupplemento);
        $this->setOrdineVisualizzazione($ordineVisualizzazione);
    }

    public function getIdPiatto(): ?int
    {
        return $this->idPiatto;
    }

    public function setIdPiatto(?int $idPiatto): void
    {
        if ($idPiatto !== null && $idPiatto <= 0) {
            throw new InvalidArgumentException('ID piatto non valido.');
        }

        $this->idPiatto = $idPiatto;
    }

    public function getIdMenu(): ?int
    {
        return $this->idMenu;
    }

    public function setIdMenu(?int $idMenu): void
    {
        if ($idMenu !== null && $idMenu <= 0) {
            throw new InvalidArgumentException('ID menu non valido.');
        }

        $this->idMenu = $idMenu;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function setNome(string $nome): void
    {
        $this->nome = trim($nome);
    }

    public function getCategoria(): string
    {
        return $this->categoria;
    }

    public function setCategoria(string $categoria): void
    {
        $categoria = strtolower(trim($categoria));
        $categorieAmmesse = [
            self::CATEGORIA_ANTIPASTO,
            self::CATEGORIA_PRIMO,
            self::CATEGORIA_SECONDO,
            self::CATEGORIA_CONTORNO,
            self::CATEGORIA_DOLCE,
            self::CATEGORIA_BEVANDA,
            self::CATEGORIA_ALTRO
        ];

        if (!in_array($categoria, $categorieAmmesse, true)) {
            throw new InvalidArgumentException('Categoria piatto non valida.');
        }

        $this->categoria = $categoria;
    }

    public function getDescrizione(): string
    {
        return $this->descrizione;
    }

    public function setDescrizione(string $descrizione): void
    {
        $this->descrizione = trim($descrizione);
    }

    public function getIngredienti(): string
    {
        return $this->ingredienti;
    }

    public function setIngredienti(string $ingredienti): void
    {
        $this->ingredienti = trim($ingredienti);
    }

    public function getAllergeni(): string
    {
        return $this->allergeni;
    }

    public function setAllergeni(string $allergeni): void
    {
        $this->allergeni = trim($allergeni);
    }

    public function getPrezzoSupplemento(): float
    {
        return $this->prezzoSupplemento;
    }

    public function setPrezzoSupplemento(float $prezzoSupplemento): void
    {
        if ($prezzoSupplemento < 0) {
            throw new InvalidArgumentException('Prezzo supplemento non valido.');
        }

        $this->prezzoSupplemento = round($prezzoSupplemento, 2);
    }

    public function getOrdineVisualizzazione(): int
    {
        return $this->ordineVisualizzazione;
    }

    public function setOrdineVisualizzazione(int $ordineVisualizzazione): void
    {
        if ($ordineVisualizzazione < 0) {
            throw new InvalidArgumentException('Ordine visualizzazione non valido.');
        }

        $this->ordineVisualizzazione = $ordineVisualizzazione;
    }

    public function toArray(): array
    {
        return [
            'idPiatto' => $this->idPiatto,
            'idMenu' => $this->idMenu,
            'nome' => $this->nome,
            'categoria' => $this->categoria,
            'descrizione' => $this->descrizione,
            'ingredienti' => $this->ingredienti,
            'allergeni' => $this->allergeni,
            'prezzoSupplemento' => $this->prezzoSupplemento,
            'ordineVisualizzazione' => $this->ordineVisualizzazione
        ];
    }

    public function __toString(): string
    {
        return 'Piatto #' . ($this->idPiatto ?? 'nuovo') .
            ' - Menu #' . ($this->idMenu ?? 'n/d') .
            ' - ' . $this->nome .
            ' [' . $this->categoria . ']';
    }
}
