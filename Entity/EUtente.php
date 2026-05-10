<?php
declare(strict_types=1);

/**
 * Entity base per tutti gli utenti registrati del sistema.
 */
class EUtente
{
    public const TIPO_UTENTE = 'utente';
    public const TIPO_CLIENTE = 'cliente';
    public const TIPO_CHEF = 'chef';
    public const TIPO_GESTORE = 'gestore';
    public const TIPO_ADMIN = 'admin';

    public const STATO_ATTIVO = 'attivo';
    public const STATO_SOSPESO = 'sospeso';
    public const STATO_BANNATO = 'bannato';

    private ?int $id;
    private string $nome;
    private string $cognome;
    private string $email;
    private string $passwordHash;
    private string $telefono;
    private string $tipo;
    private string $stato;

    public function __construct(
        ?int $id = null,
        string $nome = '',
        string $cognome = '',
        string $email = '',
        string $passwordHash = '',
        string $telefono = '',
        string $tipo = self::TIPO_UTENTE,
        string $stato = self::STATO_ATTIVO
    ) {
        $this->setId($id);
        $this->setNome($nome);
        $this->setCognome($cognome);
        $this->setEmail($email);
        $this->setPasswordHash($passwordHash);
        $this->setTelefono($telefono);
        $this->setTipo($tipo);
        $this->setStato($stato);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        if ($id !== null && $id <= 0) {
            throw new InvalidArgumentException('ID utente non valido.');
        }

        $this->id = $id;
    }

    public function getIdUtente(): ?int
    {
        return $this->getId();
    }

    public function setIdUtente(?int $id): void
    {
        $this->setId($id);
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function setNome(string $nome): void
    {
        $this->nome = trim($nome);
    }

    public function getCognome(): string
    {
        return $this->cognome;
    }

    public function setCognome(string $cognome): void
    {
        $this->cognome = trim($cognome);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $email = strtolower(trim($email));

        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('Email non valida.');
        }

        $this->email = $email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $passwordHash): void
    {
        $this->passwordHash = trim($passwordHash);
    }

    public function getTelefono(): string
    {
        return $this->telefono;
    }

    public function setTelefono(string $telefono): void
    {
        $telefono = trim($telefono);

        if ($telefono !== '' && !preg_match('/^[0-9+\s().-]{6,20}$/', $telefono)) {
            throw new InvalidArgumentException('Numero di telefono non valido.');
        }

        $this->telefono = $telefono;
    }

    public function getTipo(): string
    {
        return $this->tipo;
    }

    protected function setTipo(string $tipo): void
    {
        $tipo = strtolower(trim($tipo));
        $tipiConsentiti = [
            self::TIPO_UTENTE,
            self::TIPO_CLIENTE,
            self::TIPO_CHEF,
            self::TIPO_GESTORE,
            self::TIPO_ADMIN
        ];

        if (!in_array($tipo, $tipiConsentiti, true)) {
            throw new InvalidArgumentException('Tipo utente non valido.');
        }

        $this->tipo = $tipo;
    }

    public function getStato(): string
    {
        return $this->stato;
    }

    public function setStato(string $stato): void
    {
        $stato = strtolower(trim($stato));
        $statiConsentiti = [
            self::STATO_ATTIVO,
            self::STATO_SOSPESO,
            self::STATO_BANNATO
        ];

        if (!in_array($stato, $statiConsentiti, true)) {
            throw new InvalidArgumentException('Stato utente non valido.');
        }

        $this->stato = $stato;
    }

    public function isAttivo(): bool
    {
        return $this->stato === self::STATO_ATTIVO;
    }

    public function sospendi(): void
    {
        $this->stato = self::STATO_SOSPESO;
    }

    public function banna(): void
    {
        $this->stato = self::STATO_BANNATO;
    }

    public function riattiva(): void
    {
        $this->stato = self::STATO_ATTIVO;
    }

    public function toArray(bool $includeSensitive = false): array
    {
        $data = [
            'id' => $this->id,
            'nome' => $this->nome,
            'cognome' => $this->cognome,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'tipo' => $this->tipo,
            'stato' => $this->stato
        ];

        if ($includeSensitive) {
            $data['passwordHash'] = $this->passwordHash;
        }

        return $data;
    }

    public function __toString(): string
    {
        return $this->nome . ' ' . $this->cognome . ' (' . $this->email . ')';
    }
}
