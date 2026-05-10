<?php
declare(strict_types=1);

require_once __DIR__ . '/ERecensione.php';

class ERecensioneChef extends ERecensione
{
    private ?int $idChef;
    private ?int $idPrenotazioneChef;

    public function __construct(
        ?int $idRecensione = null,
        ?int $idAutore = null,
        int $punteggio = 1,
        string $commento = '',
        string $dataRecensione = '',
        string $stato = self::STATO_VISIBILE,
        ?int $idChef = null,
        ?int $idPrenotazioneChef = null
    ) {
        parent::__construct($idRecensione, $idAutore, $punteggio, $commento, $dataRecensione, $stato);
        $this->setIdChef($idChef);
        $this->setIdPrenotazioneChef($idPrenotazioneChef);
    }

    public function getIdChef(): ?int { return $this->idChef; }
    public function setIdChef(?int $idChef): void
    {
        if ($idChef !== null && $idChef <= 0) {
            throw new InvalidArgumentException('ID chef recensione non valido.');
        }
        $this->idChef = $idChef;
    }

    public function getIdPrenotazioneChef(): ?int { return $this->idPrenotazioneChef; }
    public function setIdPrenotazioneChef(?int $idPrenotazioneChef): void
    {
        if ($idPrenotazioneChef !== null && $idPrenotazioneChef <= 0) {
            throw new InvalidArgumentException('ID prenotazione chef recensione non valido.');
        }
        $this->idPrenotazioneChef = $idPrenotazioneChef;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'idChef' => $this->idChef,
            'idPrenotazioneChef' => $this->idPrenotazioneChef
        ]);
    }

    public function __toString(): string
    {
        return 'RecensioneChef #' . ($this->getIdRecensione() ?? 'nuova') . ' - Chef #' . ($this->idChef ?? 'n/d');
    }
}
