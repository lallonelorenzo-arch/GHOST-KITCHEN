<?php

declare(strict_types=1);

/**
 * Entity Attrezzatura.
 *
 * Rappresenta una tipologia di attrezzatura disponibile
 * in una specifica ghost kitchen.
 */
class EAttrezzatura
{
    private ?int $idAttrezzatura;
    private ?int $idGhostKitchen;
    private string $nome;
    private string $categoria;
    private string $descrizione;
    private int $quantita;

    public function __construct(
        ?int $idAttrezzatura = null,
        ?int $idGhostKitchen = null,
        string $nome = '',
        string $categoria = '',
        string $descrizione = '',
        int $quantita = 0
    ) {
        $this->setIdAttrezzatura($idAttrezzatura);
        $this->setIdGhostKitchen($idGhostKitchen);
        $this->setNome($nome);
        $this->setCategoria($categoria);
        $this->setDescrizione($descrizione);
        $this->setQuantita($quantita);
    }

    public function getIdAttrezzatura(): ?int
    {
        return $this->idAttrezzatura;
    }

    public function setIdAttrezzatura(?int $idAttrezzatura): void
    {
        if ($idAttrezzatura !== null && $idAttrezzatura <= 0) {
            throw new InvalidArgumentException('ID attrezzatura non valido.');
        }

        $this->idAttrezzatura = $idAttrezzatura;
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
        $this->categoria = trim($categoria);
    }

    public function getDescrizione(): string
    {
        return $this->descrizione;
    }

    public function setDescrizione(string $descrizione): void
    {
        $this->descrizione = trim($descrizione);
    }

    public function getQuantita(): int
    {
        return $this->quantita;
    }

    public function setQuantita(int $quantita): void
    {
        if ($quantita < 0) {
            throw new InvalidArgumentException('Quantità attrezzatura non valida.');
        }

        $this->quantita = $quantita;
    }

    public function toArray(): array
    {
        return [
            'idAttrezzatura' => $this->idAttrezzatura,
            'idGhostKitchen' => $this->idGhostKitchen,
            'nome' => $this->nome,
            'categoria' => $this->categoria,
            'descrizione' => $this->descrizione,
            'quantita' => $this->quantita
        ];
    }

    public function __toString(): string
    {
        return 'Attrezzatura #' . ($this->idAttrezzatura ?? 'nuova') .
            ' - GhostKitchen #' . ($this->idGhostKitchen ?? 'n/d') .
            ' - ' . $this->nome .
            ' (' . $this->categoria . ')' .
            ' x' . $this->quantita;
    }
}

?>