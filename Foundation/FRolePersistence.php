<?php
declare(strict_types=1);

require_once __DIR__ . '/FConnectionDB.php';
require_once __DIR__ . '/../Entity/EUtente.php';

/**
 * @internal Helper tecnico della Foundation. Non usare dai Control.
 */
class FRolePersistence
{
    public static function connection(): PDO { return FConnectionDB::getInstance()->getConnection(); }

    public static function existsInRoleTable(int $id, string $table): bool
    {
        return self::run('exist role', static function () use ($id, $table): bool {
            $statement = self::connection()->prepare(sprintf('SELECT 1 FROM %s WHERE id_utente = :id LIMIT 1', $table));
            $statement->execute(['id' => $id]);
            return $statement->fetchColumn() !== false;
        });
    }

    public static function storeRole(EUtente $utente, string $table): bool|int
    {
        return self::run('store role', static function () use ($utente, $table): bool|int {
            $id = $utente->getIdUtente();
            if ($id === null) {
                $id = (int) FUtente::store($utente);
                $utente->setIdUtente($id);
            } else {
                FUtente::update($utente);
            }

            $statement = self::connection()->prepare(sprintf('INSERT IGNORE INTO %s (id_utente) VALUES (:id)', $table));
            $statement->execute(['id' => $id]);
            return $id;
        });
    }

    public static function deleteRole(int $id, string $table): bool
    {
        return self::run('delete role', static function () use ($id, $table): bool {
            $statement = self::connection()->prepare(sprintf('DELETE FROM %s WHERE id_utente = :id', $table));
            $statement->execute(['id' => $id]);
            return $statement->rowCount() > 0;
        });
    }

    public static function run(string $operation, callable $callback): mixed
    {
        try {
            return $callback();
        } catch (PDOException $exception) {
            throw new RuntimeException('Errore Foundation durante: ' . $operation . '.', 0, $exception);
        }
    }
}
