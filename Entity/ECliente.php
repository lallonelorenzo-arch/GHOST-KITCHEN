<?php
declare(strict_types=1);

require_once __DIR__ . '/EUtente.php';

/**
 * Entity Cliente.
 */
class ECliente extends EUtente
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
            self::TIPO_CLIENTE,
            $stato
        );
    }

    public function getIdCliente(): ?int
    {
        return $this->getIdUtente();
    }

    public function setIdCliente(?int $idCliente): void
    {
        $this->setIdUtente($idCliente);
    }
}
