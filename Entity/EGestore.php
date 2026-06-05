<?php
declare(strict_types=1);

require_once __DIR__ . '/EUtente.php';

/**
 * Entity Gestore.
 */
class EGestore extends EUtente
{
    public const STATO_VERIFICA_NON_VERIFICATO = 'non_verificato';
    public const STATO_VERIFICA_IN_ATTESA = 'in_attesa';
    public const STATO_VERIFICA_VERIFICATO = 'verificato';
    public const STATO_VERIFICA_RIFIUTATO = 'rifiutato';
    public const STATO_VERIFICA_SOSPESO = 'sospeso';

    private string $statoVerifica;
    public function __construct(
        ?int $id = null,
        string $nome = '',
        string $cognome = '',
        string $email = '',
        string $passwordHash = '',
        string $telefono = '',
        string $stato = self::STATO_ATTIVO,
        string $statoVerifica = self::STATO_VERIFICA_VERIFICATO
    ) {
        parent::__construct(
            $id,
            $nome,
            $cognome,
            $email,
            $passwordHash,
            $telefono,
            self::TIPO_GESTORE,
            $stato
        );

        $this->setStatoVerifica($statoVerifica);
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
            self::STATO_VERIFICA_SOSPESO,
        ];

        if (!in_array($statoVerifica, $statiAmmessi, true)) {
            throw new InvalidArgumentException('Stato verifica gestore non valido.');
        }

        $this->statoVerifica = $statoVerifica;
    }

    public function isVerificato(): bool
    {
        return $this->statoVerifica === self::STATO_VERIFICA_VERIFICATO;
    }

    public function getIdGestore(): ?int
    {
        return $this->getIdUtente();
    }

    public function setIdGestore(?int $idGestore): void
    {
        $this->setIdUtente($idGestore);
    }
}
