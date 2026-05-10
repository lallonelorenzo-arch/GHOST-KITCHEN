<?php
declare(strict_types=1);

require_once __DIR__ . '/ERecensione.php';

class ERecensioneGhostKitchen extends ERecensione
{
    private ?int $idGhostKitchen;
    private ?int $idPrenotazioneGhostKitchen;

    public function __construct(
        ?int $idRecensione = null,
        ?int $idAutore = null,
        int $punteggio = 1,
        string $commento = '',
        string $dataRecensione = '',
        string $stato = self::STATO_VISIBILE,
        ?int $idGhostKitchen = null,
        ?int $idPrenotazioneGhostKitchen = null
    ) {
        parent::__construct($idRecensione, $idAutore, $punteggio, $commento, $dataRecensione, $stato);
        $this->setIdGhostKitchen($idGhostKitchen);
        $this->setIdPrenotazioneGhostKitchen($idPrenotazioneGhostKitchen);
    }

    public function getIdGhostKitchen(): ?int { return $this->idGhostKitchen; }
    public function setIdGhostKitchen(?int $idGhostKitchen): void
    {
        if ($idGhostKitchen !== null && $idGhostKitchen <= 0) {
            throw new InvalidArgumentException('ID ghost kitchen recensione non valido.');
        }
        $this->idGhostKitchen = $idGhostKitchen;
    }

    public function getIdPrenotazioneGhostKitchen(): ?int { return $this->idPrenotazioneGhostKitchen; }
    public function setIdPrenotazioneGhostKitchen(?int $idPrenotazioneGhostKitchen): void
    {
        if ($idPrenotazioneGhostKitchen !== null && $idPrenotazioneGhostKitchen <= 0) {
            throw new InvalidArgumentException('ID prenotazione ghost kitchen recensione non valido.');
        }
        $this->idPrenotazioneGhostKitchen = $idPrenotazioneGhostKitchen;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'idGhostKitchen' => $this->idGhostKitchen,
            'idPrenotazioneGhostKitchen' => $this->idPrenotazioneGhostKitchen
        ]);
    }

    public function __toString(): string
    {
        return 'RecensioneGhostKitchen #' . ($this->getIdRecensione() ?? 'nuova') . ' - GK #' . ($this->idGhostKitchen ?? 'n/d');
    }
}
