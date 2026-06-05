<?php
declare(strict_types=1);

require_once __DIR__ . '/FRolePersistence.php';
require_once __DIR__ . '/FUtente.php';
require_once __DIR__ . '/../Entity/ECliente.php';

class FCliente
{
    public static function exist(int $id): bool { return FUtente::exist($id) && in_array(EUtente::TIPO_CLIENTE, FUtente::getRuoli($id), true); }
    public static function load(int $id): ?ECliente { $u = FUtente::load($id); return self::exist($id) && $u !== null ? new ECliente($u->getIdUtente(), $u->getNome(), $u->getCognome(), $u->getEmail(), $u->getPasswordHash(), $u->getTelefono(), $u->getStato()) : null; }

    public static function loadAll(): array
    {
        return FRolePersistence::run('load all clienti', static function (): array {
            $sql = 'SELECT u.id_utente, u.nome, u.cognome, u.email, u.password_hash, u.telefono, u.stato
                    FROM utenti u INNER JOIN clienti c ON c.id_utente = u.id_utente
                    ORDER BY u.cognome ASC, u.nome ASC';
            $statement = FRolePersistence::connection()->prepare($sql);
            $statement->execute();

            return array_map(
                static fn (array $row): ECliente => new ECliente((int) $row['id_utente'], (string) $row['nome'], (string) $row['cognome'], (string) $row['email'], (string) $row['password_hash'], (string) ($row['telefono'] ?? ''), (string) $row['stato']),
                $statement->fetchAll()
            );
        });
    }
    public static function store(ECliente $cliente): bool|int { return FRolePersistence::storeRole($cliente, 'clienti'); }
    public static function update(ECliente $cliente): bool { return FUtente::update($cliente); }
    public static function delete(int $id): bool { return FRolePersistence::deleteRole($id, 'clienti'); }
}
