<?php
declare(strict_types=1);

require_once __DIR__ . '/EPrenotazione.php';

/**
 * Prenotazione di un servizio chef.
 */
class EPrenotazioneChef extends EPrenotazione
{
    private ?int $idChef;
    private ?int $idMenu;
    private string $indirizzoServizio;
    private int $numeroPersone;
    private string $richiesteSpeciali;
    private bool $abbinamentoVini;

    public function __construct(
        ?int $idPrenotazione = null,
        ?int $idCliente = null,
        string $dataCreazione = '',
        string $dataServizio = '',
        string $oraInizio = '',
        string $oraFine = '',
        string $stato = self::STATO_IN_ATTESA,
        float $importoTotale = 0.0,
        string $note = '',
        ?int $idChef = null,
        ?int $idMenu = null,
        string $indirizzoServizio = '',
        int $numeroPersone = 1,
        string $richiesteSpeciali = '',
        bool $abbinamentoVini = false
    ) {
        parent::__construct(
            $idPrenotazione,
            $idCliente,
            $dataCreazione,
            $dataServizio,
            $oraInizio,
            $oraFine,
            $stato,
            $importoTotale,
            $note
        );

        $this->setIdChef($idChef);
        $this->setIdMenu($idMenu);
        $this->setIndirizzoServizio($indirizzoServizio);
        $this->setNumeroPersone($numeroPersone);
        $this->setRichiesteSpeciali($richiesteSpeciali);
        $this->setAbbinamentoVini($abbinamentoVini);
    }

    public function getIdCliente(): ?int
    {
        return $this->getIdRichiedente();
    }

    public function setIdCliente(?int $idCliente): void
    {
        $this->setIdRichiedente($idCliente);
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

    public function getIndirizzoServizio(): string
    {
        return $this->indirizzoServizio;
    }

    public function setIndirizzoServizio(string $indirizzoServizio): void
    {
        $this->indirizzoServizio = trim($indirizzoServizio);
    }

    public function getNumeroPersone(): int
    {
        return $this->numeroPersone;
    }

    public function setNumeroPersone(int $numeroPersone): void
    {
        if ($numeroPersone <= 0) {
            throw new InvalidArgumentException('Numero persone non valido.');
        }

        $this->numeroPersone = $numeroPersone;
    }

    public function getRichiesteSpeciali(): string
    {
        return $this->richiesteSpeciali;
    }

    public function setRichiesteSpeciali(string $richiesteSpeciali): void
    {
        $this->richiesteSpeciali = trim($richiesteSpeciali);
    }

    public function hasAbbinamentoVini(): bool
    {
        return $this->abbinamentoVini;
    }

    public function setAbbinamentoVini(bool $abbinamentoVini): void
    {
        $this->abbinamentoVini = $abbinamentoVini;
    }

    public function validaPerConferma(): void
    {
        if ($this->getIdRichiedente() === null) {
            throw new InvalidArgumentException('Impossibile confermare: idCliente/idRichiedente mancante.');
        }

        if ($this->idChef === null) {
            throw new InvalidArgumentException('Impossibile confermare: idChef mancante.');
        }

        if ($this->idMenu === null) {
            throw new InvalidArgumentException('Impossibile confermare: idMenu mancante.');
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

        if ($this->indirizzoServizio === '') {
            throw new InvalidArgumentException('Impossibile confermare: indirizzoServizio obbligatorio.');
        }

        if ($this->numeroPersone <= 0) {
            throw new InvalidArgumentException('Impossibile confermare: numeroPersone deve essere > 0.');
        }

        if ($this->getImportoTotale() < 0) {
            throw new InvalidArgumentException('Impossibile confermare: importoTotale non valido.');
        }
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'idChef' => $this->idChef,
            'idMenu' => $this->idMenu,
            'indirizzoServizio' => $this->indirizzoServizio,
            'numeroPersone' => $this->numeroPersone,
            'richiesteSpeciali' => $this->richiesteSpeciali,
            'abbinamentoVini' => $this->abbinamentoVini
        ]);
    }
}
