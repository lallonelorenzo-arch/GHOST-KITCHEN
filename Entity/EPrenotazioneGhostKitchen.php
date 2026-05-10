<?php
declare(strict_types=1);

require_once __DIR__ . '/EPrenotazione.php';

/**
 * Prenotazione di una ghost kitchen.
 */
class EPrenotazioneGhostKitchen extends EPrenotazione
{
    public const TIPO_RICHIEDENTE_CLIENTE = 'cliente';
    public const TIPO_RICHIEDENTE_CHEF = 'chef';

    private ?int $idGhostKitchen;
    private string $tipoRichiedente;

    public function __construct(
        ?int $idPrenotazione = null,
        ?int $idRichiedente = null,
        string $dataCreazione = '',
        string $dataServizio = '',
        string $oraInizio = '',
        string $oraFine = '',
        string $stato = self::STATO_IN_ATTESA,
        float $importoTotale = 0.0,
        string $note = '',
        ?int $idGhostKitchen = null,
        string $tipoRichiedente = self::TIPO_RICHIEDENTE_CLIENTE
    ) {
        parent::__construct(
            $idPrenotazione,
            $idRichiedente,
            $dataCreazione,
            $dataServizio,
            $oraInizio,
            $oraFine,
            $stato,
            $importoTotale,
            $note
        );

        $this->setIdGhostKitchen($idGhostKitchen);
        $this->setTipoRichiedente($tipoRichiedente);
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

    public function getTipoRichiedente(): string
    {
        return $this->tipoRichiedente;
    }

    public function setTipoRichiedente(string $tipoRichiedente): void
    {
        $tipoRichiedente = strtolower(trim($tipoRichiedente));
        $tipiAmmessi = [self::TIPO_RICHIEDENTE_CLIENTE, self::TIPO_RICHIEDENTE_CHEF];

        if (!in_array($tipoRichiedente, $tipiAmmessi, true)) {
            throw new InvalidArgumentException('Tipo richiedente non valido.');
        }

        $this->tipoRichiedente = $tipoRichiedente;
    }

    public function validaPerConferma(): void
    {
        if ($this->getIdRichiedente() === null) {
            throw new InvalidArgumentException('Impossibile confermare: idRichiedente mancante.');
        }

        $this->setTipoRichiedente($this->tipoRichiedente);

        if ($this->idGhostKitchen === null) {
            throw new InvalidArgumentException('Impossibile confermare: idGhostKitchen mancante.');
        }

        if ($this->getDataServizio() === '') {
            throw new InvalidArgumentException('Impossibile confermare: dataServizio obbligatoria.');
        }

        if ($this->getOraInizio() === '') {
            throw new InvalidArgumentException('Impossibile confermare: oraInizio obbligatoria.');
        }

        if ($this->getOraFine() === '') {
            throw new InvalidArgumentException('Impossibile confermare: oraFine obbligatoria.');
        }

        if ($this->getImportoTotale() < 0) {
            throw new InvalidArgumentException('Impossibile confermare: importoTotale non valido.');
        }
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'idGhostKitchen' => $this->idGhostKitchen,
            'tipoRichiedente' => $this->tipoRichiedente
        ]);
    }
}
