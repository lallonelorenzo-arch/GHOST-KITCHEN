<?php
declare(strict_types=1);

require_once __DIR__ . '/FConnectionDB.php';

abstract class FAbstractTable
{
    abstract protected static function tableName(): string;

    abstract protected static function primaryKey(): string;

    abstract protected static function columns(): array;

    abstract protected static function idFromEntity(object $entity): ?int;

    abstract protected static function valuesFromEntity(object $entity): array;

    abstract protected static function hydrate(array $row): object;

    public static function exist(int $id): bool
    {
        return static::run('verifica esistenza', static function () use ($id): bool {
            $sql = sprintf(
                'SELECT 1 FROM %s WHERE %s = :id LIMIT 1',
                static::tableName(),
                static::primaryKey()
            );
            $statement = static::connection()->prepare($sql);
            $statement->execute(['id' => $id]);

            return $statement->fetchColumn() !== false;
        });
    }

    public static function load(int $id): ?object
    {
        return static::run('caricamento per id', static function () use ($id): ?object {
            $sql = sprintf(
                'SELECT * FROM %s WHERE %s = :id LIMIT 1',
                static::tableName(),
                static::primaryKey()
            );
            $statement = static::connection()->prepare($sql);
            $statement->execute(['id' => $id]);
            $row = $statement->fetch();

            return $row !== false ? static::hydrate($row) : null;
        });
    }

    public static function loadAll(): array
    {
        return static::run('caricamento lista', static function (): array {
            $sql = sprintf('SELECT * FROM %s', static::tableName());
            $statement = static::connection()->prepare($sql);
            $statement->execute();

            return array_map(
                static fn (array $row): object => static::hydrate($row),
                $statement->fetchAll()
            );
        });
    }

    public static function store(object $entity): bool|int
    {
        return static::run('salvataggio', static function () use ($entity): bool|int {
            $values = static::valuesFromEntity($entity);
            $id = static::idFromEntity($entity);

            if ($id === null) {
                unset($values[static::primaryKey()]);
            } else {
                $values[static::primaryKey()] = $id;
            }

            $columns = array_keys($values);
            $placeholders = array_map(static fn (string $column): string => ':' . $column, $columns);
            $sql = sprintf(
                'INSERT INTO %s (%s) VALUES (%s)',
                static::tableName(),
                implode(', ', $columns),
                implode(', ', $placeholders)
            );

            $statement = static::connection()->prepare($sql);
            $statement->execute($values);

            $generatedId = (int) static::connection()->lastInsertId();
            return $generatedId > 0 ? $generatedId : true;
        });
    }

    public static function update(object $entity): bool
    {
        return static::run('aggiornamento', static function () use ($entity): bool {
            $id = static::idFromEntity($entity);
            if ($id === null) {
                return false;
            }

            $values = static::valuesFromEntity($entity);
            unset($values[static::primaryKey()]);

            $assignments = array_map(
                static fn (string $column): string => $column . ' = :' . $column,
                array_keys($values)
            );

            $sql = sprintf(
                'UPDATE %s SET %s WHERE %s = :__id',
                static::tableName(),
                implode(', ', $assignments),
                static::primaryKey()
            );

            $values['__id'] = $id;
            $statement = static::connection()->prepare($sql);

            return $statement->execute($values);
        });
    }

    public static function delete(int $id): bool
    {
        return static::run('eliminazione', static function () use ($id): bool {
            $sql = sprintf(
                'DELETE FROM %s WHERE %s = :id',
                static::tableName(),
                static::primaryKey()
            );
            $statement = static::connection()->prepare($sql);
            $statement->execute(['id' => $id]);

            return $statement->rowCount() > 0;
        });
    }

    protected static function connection(): PDO
    {
        return FConnectionDB::getInstance()->getConnection();
    }

    protected static function fetchAllWhere(string $where, array $params = []): array
    {
        return static::run('caricamento filtrato', static function () use ($where, $params): array {
            $sql = sprintf('SELECT * FROM %s WHERE %s', static::tableName(), $where);
            $statement = static::connection()->prepare($sql);
            $statement->execute($params);

            return array_map(
                static fn (array $row): object => static::hydrate($row),
                $statement->fetchAll()
            );
        });
    }

    protected static function fetchOneWhere(string $where, array $params = []): ?object
    {
        $items = static::fetchAllWhere($where . ' LIMIT 1', $params);
        return $items[0] ?? null;
    }

    protected static function run(string $operation, callable $callback): mixed
    {
        try {
            return $callback();
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Errore Foundation su ' . static::tableName() . ' durante: ' . $operation . '.',
                0,
                $exception
            );
        }
    }
}
