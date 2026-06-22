<?php
declare(strict_types=1);

require_once __DIR__ . '/FConnectionDB.php';
require_once __DIR__ . '/../Entity/EUtente.php';
require_once __DIR__ . '/../Entity/EChef.php';
require_once __DIR__ . '/../Entity/EGestore.php';
require_once __DIR__ . '/../Entity/EGhostKitchen.php';

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

    public static function addChefRole(int $idUtente, array $chefData): void
    {
        self::run('add chef role', static function () use ($idUtente, $chefData): void {
            $statement = self::connection()->prepare('INSERT INTO chef (id_utente, biografia, specializzazione, tipologia_cucina, prezzo_base, anni_esperienza, stato_verifica, valutazione_media, numero_recensioni) VALUES (:id_utente, :biografia, :specializzazione, :tipologia_cucina, :prezzo_base, :anni_esperienza, :stato_verifica, 0.00, 0)');
            $statement->execute([
                'id_utente' => $idUtente,
                'biografia' => trim((string) ($chefData['biografia'] ?? '')) ?: null,
                'specializzazione' => (string) ($chefData['specializzazione'] ?? ''),
                'tipologia_cucina' => (string) ($chefData['tipologia_cucina'] ?? ''),
                'prezzo_base' => max(0, (float) ($chefData['prezzo_base'] ?? 0)),
                'anni_esperienza' => max(0, min(EChef::MAX_ANNI_ESPERIENZA, (int) ($chefData['anni_esperienza'] ?? 0))),
                'stato_verifica' => EChef::STATO_VERIFICA_IN_ATTESA,
            ]);
        });
    }

    public static function addGestoreRole(int $idUtente, array $ghostKitchenData): void
    {
        self::run('add gestore role', static function () use ($idUtente, $ghostKitchenData): void {
            self::connection()->prepare('INSERT INTO gestori (id_utente, stato_verifica) VALUES (:id_utente, :stato_verifica)')
                ->execute(['id_utente' => $idUtente, 'stato_verifica' => EGestore::STATO_VERIFICA_IN_ATTESA]);

            $statement = self::connection()->prepare('INSERT INTO ghost_kitchen (id_gestore, nome, descrizione, indirizzo, citta, cap, prezzo_orario, capienza, mq, stato, valutazione_media, numero_recensioni) VALUES (:id_gestore, :nome, :descrizione, :indirizzo, :citta, :cap, :prezzo_orario, :capienza, :mq, :stato, 0.00, 0)');
            $statement->execute([
                'id_gestore' => $idUtente,
                'nome' => (string) ($ghostKitchenData['nome'] ?? ''),
                'descrizione' => (string) ($ghostKitchenData['descrizione'] ?? ''),
                'indirizzo' => (string) ($ghostKitchenData['indirizzo'] ?? ''),
                'citta' => (string) ($ghostKitchenData['citta'] ?? ''),
                'cap' => (string) ($ghostKitchenData['cap'] ?? ''),
                'prezzo_orario' => max(0, (float) ($ghostKitchenData['prezzo_orario'] ?? 0)),
                'capienza' => max(1, (int) ($ghostKitchenData['capienza'] ?? 1)),
                'mq' => max(1, (float) ($ghostKitchenData['mq'] ?? 1)),
                'stato' => EGhostKitchen::STATO_SOSPESA,
            ]);
        });
    }

    public static function removeProfessionalRole(int $idUtente, string $ruolo): void
    {
        self::run('remove professional role', static function () use ($idUtente, $ruolo): void {
            $connection = self::connection();
            $connection->beginTransaction();
            try {
                if ($ruolo === 'chef') {
                    self::removeChefRole($connection, $idUtente);
                } elseif ($ruolo === 'gestore') {
                    self::removeGestoreRole($connection, $idUtente);
                } else {
                    throw new InvalidArgumentException('Ruolo professionale non valido.');
                }

                $connection->commit();
            } catch (Throwable $exception) {
                if ($connection->inTransaction()) {
                    $connection->rollBack();
                }
                throw $exception;
            }
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

    private static function removeChefRole(PDO $connection, int $idChef): void
    {
        $prenotazioni = self::countWhere($connection, 'prenotazioni_chef', 'id_chef = :id', ['id' => $idChef]);
        $recensioni = self::countWhere($connection, 'recensioni_chef', 'id_chef = :id', ['id' => $idChef]);
        if ($prenotazioni > 0 || $recensioni > 0) {
            throw new InvalidArgumentException('Non posso rimuovere il ruolo chef perche esistono prenotazioni o recensioni collegate.');
        }

        $menuIds = self::idsWhere($connection, 'menu', 'id_menu', 'id_chef = :id', ['id' => $idChef]);
        $piattoIds = [];
        foreach ($menuIds as $idMenu) {
            $piattoIds = array_merge($piattoIds, self::idsWhere($connection, 'piatti', 'id_piatto', 'id_menu = :id', ['id' => $idMenu]));
        }

        self::deleteMediaOwners($connection, 'piatto', $piattoIds);
        self::deleteMediaOwners($connection, 'menu', $menuIds);
        self::deleteMediaOwners($connection, 'chef', [$idChef]);
        $connection->prepare('DELETE FROM segnalazioni WHERE tipo_target = :tipo AND id_target = :id')
            ->execute(['tipo' => 'chef', 'id' => $idChef]);
        $connection->prepare('DELETE FROM chef WHERE id_utente = :id')
            ->execute(['id' => $idChef]);
    }

    private static function removeGestoreRole(PDO $connection, int $idGestore): void
    {
        $ghostKitchenIds = self::idsWhere($connection, 'ghost_kitchen', 'id_ghost_kitchen', 'id_gestore = :id', ['id' => $idGestore]);
        foreach ($ghostKitchenIds as $idGhostKitchen) {
            $prenotazioni = self::countWhere($connection, 'prenotazioni_ghost_kitchen', 'id_ghost_kitchen = :id', ['id' => $idGhostKitchen]);
            $recensioni = self::countWhere($connection, 'recensioni_ghost_kitchen', 'id_ghost_kitchen = :id', ['id' => $idGhostKitchen]);
            if ($prenotazioni > 0 || $recensioni > 0) {
                throw new InvalidArgumentException('Non posso rimuovere il ruolo gestore perche una ghost kitchen ha prenotazioni o recensioni collegate.');
            }
        }

        foreach ($ghostKitchenIds as $idGhostKitchen) {
            self::deleteMediaOwners($connection, 'ghost_kitchen', [$idGhostKitchen]);
            $connection->prepare('DELETE FROM certificazioni WHERE tipo_owner = :tipo AND id_owner = :id')
                ->execute(['tipo' => 'ghost_kitchen', 'id' => $idGhostKitchen]);
            $connection->prepare('DELETE FROM segnalazioni WHERE tipo_target = :tipo AND id_target = :id')
                ->execute(['tipo' => 'ghost_kitchen', 'id' => $idGhostKitchen]);
            $connection->prepare('DELETE FROM ghost_kitchen WHERE id_ghost_kitchen = :id')
                ->execute(['id' => $idGhostKitchen]);
        }

        $connection->prepare('DELETE FROM gestori WHERE id_utente = :id')
            ->execute(['id' => $idGestore]);
    }

    private static function countWhere(PDO $connection, string $table, string $where, array $params): int
    {
        $statement = $connection->prepare(sprintf('SELECT COUNT(*) FROM %s WHERE %s', $table, $where));
        $statement->execute($params);
        return (int) $statement->fetchColumn();
    }

    private static function idsWhere(PDO $connection, string $table, string $column, string $where, array $params): array
    {
        $statement = $connection->prepare(sprintf('SELECT %s FROM %s WHERE %s', $column, $table, $where));
        $statement->execute($params);
        return array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
    }

    private static function deleteMediaOwners(PDO $connection, string $tipoOwner, array $ids): void
    {
        foreach (array_values(array_unique(array_map('intval', $ids))) as $idOwner) {
            if ($idOwner <= 0) {
                continue;
            }
            $connection->prepare('DELETE FROM media WHERE tipo_owner = :tipo_owner AND id_owner = :id_owner')
                ->execute(['tipo_owner' => $tipoOwner, 'id_owner' => $idOwner]);
        }
    }
}
