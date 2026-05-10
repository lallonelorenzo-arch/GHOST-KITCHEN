<?php
declare(strict_types=1);

require_once __DIR__ . '/EUtente.php';

/**
 * Entity Gestore.
 */
class EGestore extends EUtente
{
    public function __construct(
        ?int $id = null,
        string $nome = '',
        string $cognome = '',
        string $email = '',
        string $passwordHash = '',
        string $telefono = '',
        string $stato = self::STATO_ATTIVO //costante di classe per lo stato del gestore
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
