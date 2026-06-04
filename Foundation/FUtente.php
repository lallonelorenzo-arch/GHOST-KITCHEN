<?php
declare(strict_types=1);

require_once __DIR__ . '/FConnectionDB.php';
require_once __DIR__ . '/../Entity/EUtente.php';

class FUtente
{
    private const ROLE_TABLES = [
        EUtente::TIPO_CLIENTE => 'clienti',
        EUtente::TIPO_CHEF => 'chef',
        EUtente::TIPO_GESTORE => 'gestori',
        EUtente::TIPO_ADMIN => 'amministratori',
    ];

    public static function exist(int $idUtente): bool
    {
        return self::run('verifica esistenza utente', static function () use ($idUtente): bool {
            $sql = 'SELECT 1 FROM utenti WHERE id_utente = :id_utente LIMIT 1';
            $statement = self::connection()->prepare($sql);
            $statement->execute(['id_utente' => $idUtente]);

            return $statement->fetchColumn() !== false;
        });
    }

    public static function load(int $idUtente): ?EUtente
    {
        return self::run('caricamento utente per id', static function () use ($idUtente): ?EUtente {
            $extraSelect = self::optionalSelects();
            $sql = 'SELECT id_utente, nome, cognome, email, password_hash, telefono, stato, ' . implode(', ', $extraSelect) . '
                    FROM utenti
                    WHERE id_utente = :id_utente
                    LIMIT 1';
            $statement = self::connection()->prepare($sql);
            $statement->execute(['id_utente' => $idUtente]);
            $row = $statement->fetch();

            return $row !== false ? self::hydrate($row) : null;
        });
    }

    public static function loadByEmail(string $email): ?EUtente
    {
        return self::run('caricamento utente per email', static function () use ($email): ?EUtente {
            $extraSelect = self::optionalSelects();
            $sql = 'SELECT id_utente, nome, cognome, email, password_hash, telefono, stato, ' . implode(', ', $extraSelect) . '
                    FROM utenti
                    WHERE email = :email
                    LIMIT 1';
            $statement = self::connection()->prepare($sql);
            $statement->execute(['email' => strtolower(trim($email))]);
            $row = $statement->fetch();

            return $row !== false ? self::hydrate($row) : null;
        });
    }

    public static function store(EUtente $utente): bool|int
    {
        return self::run('salvataggio nuovo utente', static function () use ($utente): bool|int {
            $passwordHash = self::normalizePasswordForStorage($utente->getPasswordHash());

            $optionalColumns = self::optionalColumns();
            $columns = array_merge(['nome', 'cognome', 'email', 'password_hash', 'telefono', 'stato'], $optionalColumns);
            $placeholders = array_map(static fn (string $column): string => ':' . $column, $columns);
            $sql = 'INSERT INTO utenti (' . implode(', ', $columns) . ')
                    VALUES (' . implode(', ', $placeholders) . ')';
            $values = [
                'nome' => $utente->getNome(),
                'cognome' => $utente->getCognome(),
                'email' => $utente->getEmail(),
                'password_hash' => $passwordHash,
                'telefono' => $utente->getTelefono(),
                'stato' => $utente->getStato(),
            ];
            if (in_array('foto_profilo', $optionalColumns, true)) {
                $values['foto_profilo'] = $utente->getFotoProfilo() !== '' ? $utente->getFotoProfilo() : null;
            }
            if (in_array('localita', $optionalColumns, true)) {
                $values['localita'] = $utente->getLocalita() !== '' ? $utente->getLocalita() : null;
            }
            if (in_array('biografia', $optionalColumns, true)) {
                $values['biografia'] = $utente->getBiografia() !== '' ? $utente->getBiografia() : null;
            }
            $statement = self::connection()->prepare($sql);
            $statement->execute($values);

            $id = (int) self::connection()->lastInsertId();

            // TODO: la creazione dei ruoli va gestita in classi Foundation dedicate quando saranno implementate.
            return $id > 0 ? $id : true;
        });
    }

    public static function update(EUtente $utente): bool
    {
        return self::run('aggiornamento utente', static function () use ($utente): bool {
            $idUtente = $utente->getIdUtente();
            if ($idUtente === null) {
                return false;
            }

            $passwordHash = self::normalizePasswordForStorage($utente->getPasswordHash());

            $optionalColumns = self::optionalColumns();
            $optionalUpdates = array_map(static fn (string $column): string => ', ' . $column . ' = :' . $column, $optionalColumns);
            $sql = 'UPDATE utenti
                    SET nome = :nome,
                        cognome = :cognome,
                        email = :email,
                        password_hash = :password_hash,
                        telefono = :telefono,
                        stato = :stato' . implode('', $optionalUpdates) . '
                    WHERE id_utente = :id_utente';
            $statement = self::connection()->prepare($sql);

            $values = [
                'id_utente' => $idUtente,
                'nome' => $utente->getNome(),
                'cognome' => $utente->getCognome(),
                'email' => $utente->getEmail(),
                'password_hash' => $passwordHash,
                'telefono' => $utente->getTelefono(),
                'stato' => $utente->getStato(),
            ];
            if (in_array('foto_profilo', $optionalColumns, true)) {
                $values['foto_profilo'] = $utente->getFotoProfilo() !== '' ? $utente->getFotoProfilo() : null;
            }
            if (in_array('localita', $optionalColumns, true)) {
                $values['localita'] = $utente->getLocalita() !== '' ? $utente->getLocalita() : null;
            }
            if (in_array('biografia', $optionalColumns, true)) {
                $values['biografia'] = $utente->getBiografia() !== '' ? $utente->getBiografia() : null;
            }

            return $statement->execute($values);
        });
    }

    public static function delete(int $idUtente): bool
    {
        return self::run('eliminazione logica utente', static function () use ($idUtente): bool {
            // Il DB ha il campo stato: preferiamo una disattivazione logica alla cancellazione fisica.
            // TODO: valutare una semantica applicativa piu precisa per utenti sospesi/bannati.
            $sql = 'UPDATE utenti SET stato = :stato WHERE id_utente = :id_utente';
            $statement = self::connection()->prepare($sql);
            $statement->execute([
                'id_utente' => $idUtente,
                'stato' => EUtente::STATO_BANNATO,
            ]);

            return $statement->rowCount() > 0;
        });
    }

    public static function verificaCredenziali(string $email, string $password): ?EUtente
    {
        $utente = self::loadByEmail($email);
        if ($utente === null || !$utente->isAttivo()) {
            return null;
        }

        $storedPassword = $utente->getPasswordHash();

        return password_verify($password, $storedPassword) ? $utente : null;
    }

    public static function getRuoli(int $idUtente): array
    {
        return self::run('caricamento ruoli utente', static function () use ($idUtente): array {
            $ruoli = [];

            foreach (self::ROLE_TABLES as $ruolo => $table) {
                $sql = sprintf('SELECT 1 FROM %s WHERE id_utente = :id_utente LIMIT 1', $table);
                $statement = self::connection()->prepare($sql);
                $statement->execute(['id_utente' => $idUtente]);

                if ($statement->fetchColumn() !== false) {
                    $ruoli[] = $ruolo;
                }
            }

            return $ruoli;
        });
    }

    public static function emailExists(string $email): bool
    {
        return self::run('verifica esistenza email utente', static function () use ($email): bool {
            $sql = 'SELECT 1 FROM utenti WHERE email = :email LIMIT 1';
            $statement = self::connection()->prepare($sql);
            $statement->execute(['email' => strtolower(trim($email))]);

            return $statement->fetchColumn() !== false;
        });
    }

    private static function hydrate(array $row): EUtente
    {
        $idUtente = (int) $row['id_utente'];
        $ruoli = self::getRuoli($idUtente);

        // TODO: EUtente contiene un solo tipo, mentre il DB consente piu ruoli per lo stesso id_utente.
        return new EUtente(
            $idUtente,
            (string) $row['nome'],
            (string) $row['cognome'],
            (string) $row['email'],
            (string) $row['password_hash'],
            (string) $row['telefono'],
            self::tipoFromRuoli($ruoli),
            (string) $row['stato'],
            (string) ($row['foto_profilo'] ?? ''),
            (string) ($row['localita'] ?? ''),
            (string) ($row['biografia'] ?? '')
        );
    }

    private static function tipoFromRuoli(array $ruoli): string
    {
        if (in_array(EUtente::TIPO_ADMIN, $ruoli, true)) {
            return EUtente::TIPO_ADMIN;
        }

        foreach ([EUtente::TIPO_CLIENTE, EUtente::TIPO_CHEF, EUtente::TIPO_GESTORE] as $tipo) {
            if (in_array($tipo, $ruoli, true)) {
                return $tipo;
            }
        }

        return EUtente::TIPO_UTENTE;
    }

    private static function normalizePasswordForStorage(string $password): string
    {
        if ($password === '') {
            return $password;
        }

        $info = password_get_info($password);
        $algo = $info['algo'] ?? null;

        if ($algo !== null && $algo !== 0 && $algo !== '') {
            return $password;
        }

        return password_hash($password, PASSWORD_DEFAULT);
    }

    private static function connection(): PDO
    {
        return FConnectionDB::getInstance()->getConnection();
    }

    private static function optionalSelects(): array
    {
        return [
            self::hasColumn('foto_profilo') ? 'foto_profilo' : "'' AS foto_profilo",
            self::hasColumn('localita') ? 'localita' : "'' AS localita",
            self::hasColumn('biografia') ? 'biografia' : "'' AS biografia",
        ];
    }

    private static function optionalColumns(): array
    {
        return array_values(array_filter(
            ['foto_profilo', 'localita', 'biografia'],
            static fn (string $column): bool => self::hasColumn($column)
        ));
    }

    private static function hasColumn(string $column): bool
    {
        static $cache = [];
        if (array_key_exists($column, $cache)) {
            return $cache[$column];
        }

        $sql = "SELECT 1
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'utenti'
                  AND COLUMN_NAME = :column
                LIMIT 1";
        $statement = self::connection()->prepare($sql);
        $statement->execute(['column' => $column]);
        $cache[$column] = $statement->fetchColumn() !== false;

        return $cache[$column];
    }

    private static function run(string $operation, callable $callback): mixed
    {
        try {
            return $callback();
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Errore nella persistenza utente durante: ' . $operation . '.',
                0,
                $exception
            );
        }
    }
}
