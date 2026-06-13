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

    public const SIGLE_PROVINCE_ITALIANE = [
        'AG', 'AL', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AT', 'AV',
        'BA', 'BG', 'BI', 'BL', 'BN', 'BO', 'BR', 'BS', 'BT', 'BZ',
        'CA', 'CB', 'CE', 'CH', 'CI', 'CL', 'CN', 'CO', 'CR', 'CS', 'CT', 'CZ',
        'EN',
        'FC', 'FE', 'FG', 'FI', 'FM', 'FR',
        'GE', 'GO', 'GR',
        'IM', 'IS',
        'KR',
        'LC', 'LE', 'LI', 'LO', 'LT', 'LU',
        'MB', 'MC', 'ME', 'MI', 'MN', 'MO', 'MS', 'MT',
        'NA', 'NO', 'NU',
        'OG', 'OR', 'OT',
        'PA', 'PC', 'PD', 'PE', 'PG', 'PI', 'PN', 'PO', 'PR', 'PT', 'PU', 'PV', 'PZ',
        'RA', 'RC', 'RE', 'RG', 'RI', 'RM', 'RN', 'RO',
        'SA', 'SI', 'SO', 'SP', 'SR', 'SS', 'SU', 'SV',
        'TA', 'TE', 'TN', 'TO', 'TP', 'TR', 'TS', 'TV',
        'UD',
        'VA', 'VB', 'VC', 'VE', 'VI', 'VR', 'VS', 'VT', 'VV',
    ];

    private ?int $id;
    private string $nome;
    private string $cognome;
    private string $email;
    private string $passwordHash;
    private string $telefono;
    private string $tipo;
    private string $stato;
    private string $fotoProfilo;
    private string $localita;
    private string $biografia;
    private string $via;
    private string $citta;
    private string $numeroCivico;
    private string $indirizzo;
    private string $provincia;

    public function __construct(
        ?int $id = null,
        string $nome = '',
        string $cognome = '',
        string $email = '',
        string $passwordHash = '',
        string $telefono = '',
        string $tipo = self::TIPO_UTENTE,
        string $stato = self::STATO_ATTIVO,
        string $fotoProfilo = '',
        string $localita = '',
        string $biografia = '',
        string $via = '',
        string $citta = '',
        string $numeroCivico = '',
        string $indirizzo = '',
        string $provincia = ''
    ) {
        $this->setId($id);
        $this->setNome($nome);
        $this->setCognome($cognome);
        $this->setEmail($email);
        $this->setPasswordHash($passwordHash);
        $this->setTelefono($telefono);
        $this->setTipo($tipo);
        $this->setStato($stato);
        $this->setFotoProfilo($fotoProfilo);
        $this->setLocalita($localita);
        $this->setBiografia($biografia);
        $this->setVia($via);
        $this->setCitta($citta);
        $this->setNumeroCivico($numeroCivico);
        $this->setIndirizzo($indirizzo !== '' ? $indirizzo : $via);
        $this->setProvincia($provincia);
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

    public function getFotoProfilo(): string
    {
        return $this->fotoProfilo;
    }

    public function getLocalita(): string
    {
        return $this->localita;
    }

    public function setLocalita(string $localita): void
    {
        $this->localita = trim($localita);
    }

    public function getBiografia(): string
    {
        return $this->biografia;
    }

    public function setBiografia(string $biografia): void
    {
        $this->biografia = trim($biografia);
    }

    public function getVia(): string
    {
        return $this->via;
    }

    public function setVia(string $via): void
    {
        $this->via = trim($via);
    }

    public function getIndirizzo(): string
    {
        return $this->indirizzo !== '' ? $this->indirizzo : $this->via;
    }

    public function setIndirizzo(string $indirizzo): void
    {
        $this->indirizzo = trim($indirizzo);
    }

    public function getCitta(): string
    {
        return $this->citta;
    }

    public function setCitta(string $citta): void
    {
        $this->citta = trim($citta);
    }

    public function getNumeroCivico(): string
    {
        return $this->numeroCivico;
    }

    public function setNumeroCivico(string $numeroCivico): void
    {
        $this->numeroCivico = trim($numeroCivico);
    }

    public function getProvincia(): string
    {
        return $this->provincia;
    }

    public function setProvincia(string $provincia): void
    {
        $this->provincia = trim($provincia);
    }

    public static function isProvinciaItaliana(string $provincia): bool
    {
        return in_array(strtoupper(trim($provincia)), self::SIGLE_PROVINCE_ITALIANE, true);
    }

    public function setFotoProfilo(string $fotoProfilo): void
    {
        $this->fotoProfilo = trim($fotoProfilo);
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
            'stato' => $this->stato,
            'fotoProfilo' => $this->fotoProfilo,
            'localita' => $this->localita,
            'biografia' => $this->biografia,
            'via' => $this->via,
            'citta' => $this->citta,
            'numeroCivico' => $this->numeroCivico,
            'indirizzo' => $this->getIndirizzo(),
            'provincia' => $this->provincia
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
