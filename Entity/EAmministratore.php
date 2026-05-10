<?php
declare(strict_types=1);

require_once __DIR__ . '/EUtente.php';

/**
 * Entity Amministratore.
 */
class EAmministratore extends EUtente
{
    public function __construct(
        ?int $id = null,
        string $nome = '',
        string $cognome = '',
        string $email = '',
        string $passwordHash = '',
        string $telefono = '',
        string $stato = self::STATO_ATTIVO
    ) {
        parent::__construct(
            $id,
            $nome,
            $cognome,
            $email,
            $passwordHash,
            $telefono,
            self::TIPO_ADMIN,
            $stato
        );
    }

    public function getIdAmministratore(): ?int
    {
        return $this->getIdUtente();
    }

    public function setIdAmministratore(?int $idAmministratore): void
    {
        $this->setIdUtente($idAmministratore);
    }
}
