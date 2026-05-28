<?php
declare(strict_types=1);

require_once __DIR__ . '/FAbstractTable.php';
require_once __DIR__ . '/../Entity/EDisponibilitaGhostKitchen.php';

class FDisponibilitaGhostKitchen extends FAbstractTable
{
    protected static function tableName(): string { return 'disponibilita_ghost_kitchen'; }
    protected static function primaryKey(): string { return 'id_disponibilita_ghost_kitchen'; }
    protected static function columns(): array { return ['id_disponibilita_ghost_kitchen', 'id_ghost_kitchen', 'data', 'ora_inizio', 'ora_fine', 'stato']; }
    protected static function idFromEntity(object $entity): ?int { return $entity->getIdDisponibilitaGhostKitchen(); }
    protected static function valuesFromEntity(object $entity): array
    {
        return ['id_disponibilita_ghost_kitchen' => $entity->getIdDisponibilitaGhostKitchen(), 'id_ghost_kitchen' => $entity->getIdGhostKitchen(), 'data' => $entity->getData(), 'ora_inizio' => $entity->getOraInizio(), 'ora_fine' => $entity->getOraFine(), 'stato' => $entity->getStato()];
    }
    protected static function hydrate(array $row): EDisponibilitaGhostKitchen
    {
        return new EDisponibilitaGhostKitchen((int) $row['id_disponibilita_ghost_kitchen'], (int) $row['id_ghost_kitchen'], (string) $row['data'], (string) $row['ora_inizio'], (string) $row['ora_fine'], (string) $row['stato']);
    }
    public static function loadByGhostKitchen(int $idGhostKitchen): array
    {
        return static::fetchAllWhere('id_ghost_kitchen = :id', ['id' => $idGhostKitchen]);
    }

    public static function verificaDisponibilita(int $idGhostKitchen, string $data, string $oraInizio, string $oraFine): bool
    {
        return static::run('verifica disponibilita ghost kitchen', static function () use ($idGhostKitchen, $data, $oraInizio, $oraFine): bool {
            $sql = 'SELECT 1 FROM disponibilita_ghost_kitchen
                    WHERE id_ghost_kitchen = :id_ghost_kitchen AND data = :data
                      AND ora_inizio = :ora_inizio AND ora_fine = :ora_fine
                      AND stato = :stato LIMIT 1';
            $statement = static::connection()->prepare($sql);
            $statement->execute(['id_ghost_kitchen' => $idGhostKitchen, 'data' => $data, 'ora_inizio' => $oraInizio, 'ora_fine' => $oraFine, 'stato' => EDisponibilitaGhostKitchen::STATO_LIBERA]);

            return $statement->fetchColumn() !== false;
        });
    }
}
