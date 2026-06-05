<?php
declare(strict_types=1);

require_once __DIR__ . '/FRolePersistence.php';
require_once __DIR__ . '/FUtente.php';
require_once __DIR__ . '/../Entity/EGestore.php';

class FGestore
{
    public static function exist(int $id): bool { return FUtente::exist($id) && in_array(EUtente::TIPO_GESTORE, FUtente::getRuoli($id), true); }

    public static function load(int $id): ?EGestore
    {
        return FRolePersistence::run('load gestore', static function () use ($id): ?EGestore {
            $sql = 'SELECT u.id_utente, u.nome, u.cognome, u.email, u.password_hash, u.telefono, u.stato, g.stato_verifica
                    FROM utenti u INNER JOIN gestori g ON g.id_utente = u.id_utente
                    WHERE u.id_utente = :id LIMIT 1';
            $statement = FRolePersistence::connection()->prepare($sql);
            $statement->execute(['id' => $id]);
            $row = $statement->fetch();

            return $row !== false ? self::hydrate($row) : null;
        });
    }

    public static function loadAll(): array
    {
        return FRolePersistence::run('load all gestori', static function (): array {
            $sql = 'SELECT u.id_utente, u.nome, u.cognome, u.email, u.password_hash, u.telefono, u.stato, g.stato_verifica
                    FROM utenti u INNER JOIN gestori g ON g.id_utente = u.id_utente
                    ORDER BY u.cognome ASC, u.nome ASC';
            $statement = FRolePersistence::connection()->prepare($sql);
            $statement->execute();

            return array_map(static fn (array $row): EGestore => self::hydrate($row), $statement->fetchAll());
        });
    }

    public static function store(EGestore $gestore): bool|int { return FRolePersistence::storeRole($gestore, 'gestori'); }

    public static function update(EGestore $gestore): bool
    {
        return FRolePersistence::run('update gestore', static function () use ($gestore): bool {
            if ($gestore->getIdGestore() === null) {
                return false;
            }

            FUtente::update($gestore);
            $sql = 'UPDATE gestori SET stato_verifica = :stato_verifica WHERE id_utente = :id_utente';
            return FRolePersistence::connection()->prepare($sql)->execute([
                'id_utente' => $gestore->getIdGestore(),
                'stato_verifica' => $gestore->getStatoVerifica(),
            ]);
        });
    }

    public static function delete(int $id): bool { return FRolePersistence::deleteRole($id, 'gestori'); }

    private static function hydrate(array $row): EGestore
    {
        return new EGestore((int) $row['id_utente'], (string) $row['nome'], (string) $row['cognome'], (string) $row['email'], (string) $row['password_hash'], (string) ($row['telefono'] ?? ''), (string) $row['stato'], (string) ($row['stato_verifica'] ?? EGestore::STATO_VERIFICA_VERIFICATO));
    }
}
