<?php
declare(strict_types=1);

require_once __DIR__ . '/EUtente.php';

/**
 * Entity Chef.
 */
class EChef extends EUtente
{
    public const STATO_VERIFICA_NON_VERIFICATO = 'non_verificato';
    public const STATO_VERIFICA_IN_ATTESA = 'in_attesa';
    public const STATO_VERIFICA_VERIFICATO = 'verificato';
    public const STATO_VERIFICA_RIFIUTATO = 'rifiutato';
    public const STATO_VERIFICA_SOSPESO = 'sospeso';

    private string $biografia;
    private string $specializzazione;
    private string $tipologiaCucina;
    private float $prezzoBase;
    private int $anniEsperienza;
    private string $statoVerifica;
    private float $valutazioneMedia;
    private int $numeroRecensioni;

    public function __construct(
        ?int $id = null,
        string $nome = '',
        string $cognome = '',
        string $email = '',
        string $passwordHash = '',
        string $telefono = '',
        string $stato = self::STATO_ATTIVO,
        string $fotoProfilo = '',
        string $localita = '',
        string $biografiaUtente = '',
        string $biografia = '',
        string $specializzazione = '',
        string $tipologiaCucina = '',
        float $prezzoBase = 0.0,
        int $anniEsperienza = 0,
        string $statoVerifica = self::STATO_VERIFICA_NON_VERIFICATO,
        float $valutazioneMedia = 0.0,
        int $numeroRecensioni = 0
    ) {
        parent::__construct(
            $id,
            $nome,
            $cognome,
            $email,
            $passwordHash,
            $telefono,
            self::TIPO_CHEF,
            $stato,
            $fotoProfilo,
            $localita,
            $biografiaUtente
        );

        $this->setBiografia($biografia);
        $this->setSpecializzazione($specializzazione);
        $this->setTipologiaCucina($tipologiaCucina);
        $this->setPrezzoBase($prezzoBase);
        $this->setAnniEsperienza($anniEsperienza);
        $this->setStatoVerifica($statoVerifica);
        $this->setValutazioneMedia($valutazioneMedia);
        $this->setNumeroRecensioni($numeroRecensioni);
    }

    public function getIdChef(): ?int
    {
        return $this->getId();
    }

    public function setIdChef(?int $idChef): void
    {
        $this->setId($idChef);
    }

    public function getBiografia(): string
    {
        return $this->biografia;
    }

    public function setBiografia(string $biografia): void
    {
        $this->biografia = trim($biografia);
    }

    public function getSpecializzazione(): string
    {
        return $this->specializzazione;
    }

    public function setSpecializzazione(string $specializzazione): void
    {
        $this->specializzazione = trim($specializzazione);
    }

    public function getPrezzoBase(): float
    {
        return $this->prezzoBase;
    }

    public function getTipologiaCucina(): string
    {
        return $this->tipologiaCucina;
    }

    public function setTipologiaCucina(string $tipologiaCucina): void
    {
        $this->tipologiaCucina = trim($tipologiaCucina);
    }

    public function setPrezzoBase(float $prezzoBase): void
    {
        if ($prezzoBase < 0) {
            throw new InvalidArgumentException('Il prezzo base deve essere >= 0.');
        }

        $this->prezzoBase = round($prezzoBase, 2);
    }

    public function getAnniEsperienza(): int
    {
        return $this->anniEsperienza;
    }

    public function setAnniEsperienza(int $anniEsperienza): void
    {
        if ($anniEsperienza < 0) {
            throw new InvalidArgumentException('Gli anni di esperienza devono essere >= 0.');
        }

        $this->anniEsperienza = $anniEsperienza;
    }

    public function getStatoVerifica(): string
    {
        return $this->statoVerifica;
    }

    public function setStatoVerifica(string $statoVerifica): void
    {
        $statoVerifica = strtolower(trim($statoVerifica));
        $statiAmmessi = [
            self::STATO_VERIFICA_NON_VERIFICATO,
            self::STATO_VERIFICA_IN_ATTESA,
            self::STATO_VERIFICA_VERIFICATO,
            self::STATO_VERIFICA_RIFIUTATO,
            self::STATO_VERIFICA_SOSPESO
        ];

        if (!in_array($statoVerifica, $statiAmmessi, true)) {
            throw new InvalidArgumentException('Stato verifica chef non valido.');
        }

        $this->statoVerifica = $statoVerifica;
    }

    public function getValutazioneMedia(): float
    {
        return $this->valutazioneMedia;
    }

    public function setValutazioneMedia(float $valutazioneMedia): void
    {
        if ($valutazioneMedia < 0 || $valutazioneMedia > 5) {
            throw new InvalidArgumentException('La valutazione media deve essere tra 0 e 5.');
        }

        $this->valutazioneMedia = round($valutazioneMedia, 2);
    }

    public function getNumeroRecensioni(): int
    {
        return $this->numeroRecensioni;
    }

    public function setNumeroRecensioni(int $numeroRecensioni): void
    {
        if ($numeroRecensioni < 0) {
            throw new InvalidArgumentException('Il numero recensioni deve essere >= 0.');
        }

        $this->numeroRecensioni = $numeroRecensioni;
    }

    public function isVerificato(): bool
    {
        return $this->statoVerifica === self::STATO_VERIFICA_VERIFICATO;
    }

    public function richiediVerifica(): void
    {
        $this->statoVerifica = self::STATO_VERIFICA_IN_ATTESA;
    }

    public function approvaVerifica(): void
    {
        $this->statoVerifica = self::STATO_VERIFICA_VERIFICATO;
    }

    public function rifiutaVerifica(): void
    {
        $this->statoVerifica = self::STATO_VERIFICA_RIFIUTATO;
    }

    public function sospendiVerifica(): void
    {
        $this->statoVerifica = self::STATO_VERIFICA_SOSPESO;
    }

    public function aggiornaValutazioneMedia(float $nuovoPunteggio): void
    {
        if ($nuovoPunteggio < 1 || $nuovoPunteggio > 5) {
            throw new InvalidArgumentException('Il nuovo punteggio deve essere tra 1 e 5.');
        }

        $somma = $this->valutazioneMedia * $this->numeroRecensioni;
        $somma += $nuovoPunteggio;

        $this->numeroRecensioni++;
        $this->valutazioneMedia = round($somma / $this->numeroRecensioni, 2);
    }

    public function toArray(bool $includeSensitive = false): array
    {
        return array_merge(parent::toArray($includeSensitive), [
            'biografia' => $this->biografia,
            'specializzazione' => $this->specializzazione,
            'tipologiaCucina' => $this->tipologiaCucina,
            'prezzoBase' => $this->prezzoBase,
            'anniEsperienza' => $this->anniEsperienza,
            'statoVerifica' => $this->statoVerifica,
            'valutazioneMedia' => $this->valutazioneMedia,
            'numeroRecensioni' => $this->numeroRecensioni
        ]);
    }

    public function __toString(): string
    {
        return 'Chef #' . ($this->getId() ?? 'nuovo') . ' ' . $this->getNome() . ' ' . $this->getCognome();
    }
}
