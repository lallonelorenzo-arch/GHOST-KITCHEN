<?php
declare(strict_types=1);

class EMenu
{
    private ?int $idMenu;
    private ?int $idChef;
    private string $nome;
    private string $descrizione;
    private float $prezzoPersona;
    private bool $attivo;

    public function __construct(
        ?int $idMenu = null,
        ?int $idChef = null,
        string $nome = '',
        string $descrizione = '',
        float $prezzoPersona = 0.0,
        bool $attivo = true
    ) {
        $this->setIdMenu($idMenu);
        $this->setIdChef($idChef);
        $this->setNome($nome);
        $this->setDescrizione($descrizione);
        $this->setPrezzoPersona($prezzoPersona);
        $this->setAttivo($attivo);
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

    public function getPrezzoPersona(): float
    {
        return $this->prezzoPersona;
    }

    public function setPrezzoPersona(float $prezzoPersona): void
    {
        if ($prezzoPersona < 0) {
            throw new InvalidArgumentException('Prezzo per persona non valido.');
        }

        $this->prezzoPersona = round($prezzoPersona, 2);
    }

    public function isAttivo(): bool
    {
        return $this->attivo;
    }

    public function setAttivo(bool $attivo): void
    {
        $this->attivo = $attivo;
    }

    public function toArray(): array
    {
        return [
            'idMenu' => $this->idMenu,
            'idChef' => $this->idChef,
            'nome' => $this->nome,
            'descrizione' => $this->descrizione,
            'prezzoPersona' => $this->prezzoPersona,
            'attivo' => $this->attivo
        ];
    }

    public function __toString(): string
    {
        return 'Menu #' . ($this->idMenu ?? 'nuovo') .
            ' - Chef #' . ($this->idChef ?? 'n/d') .
            ' - ' . $this->nome .
            ' - €/persona ' . $this->prezzoPersona .
            ' [' . ($this->attivo ? 'attivo' : 'non_attivo') . ']';
    }
}
