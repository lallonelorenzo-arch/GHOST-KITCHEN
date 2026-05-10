<?php
declare(strict_types=1);

abstract class ERecensione
{
    public const STATO_VISIBILE = 'visibile';
    public const STATO_NASCOSTA = 'nascosta';
    public const STATO_RIMOSSA = 'rimossa';

    private ?int $idRecensione;
    private ?int $idAutore;
    private int $punteggio;
    private string $commento;
    private string $dataRecensione;
    private string $stato;

    public function __construct(
        ?int $idRecensione = null,
        ?int $idAutore = null,
        int $punteggio = 1,
        string $commento = '',
        string $dataRecensione = '',
        string $stato = self::STATO_VISIBILE
    ) {
        $this->setIdRecensione($idRecensione);
        $this->setIdAutore($idAutore);
        $this->setPunteggio($punteggio);
        $this->setCommento($commento);
        $this->setDataRecensione($dataRecensione);
        $this->setStato($stato);
    }

    public function getIdRecensione(): ?int { return $this->idRecensione; }
    public function setIdRecensione(?int $idRecensione): void
    {
        if ($idRecensione !== null && $idRecensione <= 0) {
            throw new InvalidArgumentException('ID recensione non valido.');
        }
        $this->idRecensione = $idRecensione;
    }

    public function getIdAutore(): ?int { return $this->idAutore; }
    public function setIdAutore(?int $idAutore): void
    {
        if ($idAutore !== null && $idAutore <= 0) {
            throw new InvalidArgumentException('ID autore non valido.');
        }
        $this->idAutore = $idAutore;
    }

    public function getPunteggio(): int { return $this->punteggio; }
    public function setPunteggio(int $punteggio): void
    {
        if ($punteggio < 1 || $punteggio > 5) {
            throw new InvalidArgumentException('Punteggio recensione non valido.');
        }
        $this->punteggio = $punteggio;
    }

    public function getCommento(): string { return $this->commento; }
    public function setCommento(string $commento): void { $this->commento = trim($commento); }

    public function getDataRecensione(): string { return $this->dataRecensione; }
    public function setDataRecensione(string $dataRecensione): void { $this->dataRecensione = trim($dataRecensione); }

    public function getStato(): string { return $this->stato; }
    public function setStato(string $stato): void
    {
        $stato = strtolower(trim($stato));
        $ammessi = [self::STATO_VISIBILE, self::STATO_NASCOSTA, self::STATO_RIMOSSA];
        if (!in_array($stato, $ammessi, true)) {
            throw new InvalidArgumentException('Stato recensione non valido.');
        }
        $this->stato = $stato;
    }

    public function nascondi(): void { $this->stato = self::STATO_NASCOSTA; }
    public function rimuovi(): void { $this->stato = self::STATO_RIMOSSA; }
    public function ripristina(): void { $this->stato = self::STATO_VISIBILE; }
    public function isVisibile(): bool { return $this->stato === self::STATO_VISIBILE; }

    public function toArray(): array
    {
        return [
            'idRecensione' => $this->idRecensione,
            'idAutore' => $this->idAutore,
            'punteggio' => $this->punteggio,
            'commento' => $this->commento,
            'dataRecensione' => $this->dataRecensione,
            'stato' => $this->stato
        ];
    }

    public function __toString(): string
    {
        return 'Recensione #' . ($this->idRecensione ?? 'nuova') . ' [' . $this->stato . ']';
    }
}
