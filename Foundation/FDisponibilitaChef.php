<?php
declare(strict_types=1);

require_once __DIR__ . '/FAbstractTable.php';
require_once __DIR__ . '/../Entity/EDisponibilitaChef.php';

class FDisponibilitaChef extends FAbstractTable
{
    protected static function tableName(): string { return 'disponibilita_chef'; }
    protected static function primaryKey(): string { return 'id_disponibilita_chef'; }
    protected static function columns(): array { return ['id_disponibilita_chef', 'id_chef', 'data', 'ora_inizio', 'ora_fine', 'stato']; }
    protected static function idFromEntity(object $entity): ?int { return $entity->getIdDisponibilitaChef(); }
    protected static function valuesFromEntity(object $entity): array
    {
        return ['id_disponibilita_chef' => $entity->getIdDisponibilitaChef(), 'id_chef' => $entity->getIdChef(), 'data' => $entity->getData(), 'ora_inizio' => $entity->getOraInizio(), 'ora_fine' => $entity->getOraFine(), 'stato' => $entity->getStato()];
    }
    protected static function hydrate(array $row): EDisponibilitaChef
    {
        return new EDisponibilitaChef((int) $row['id_disponibilita_chef'], (int) $row['id_chef'], (string) $row['data'], (string) $row['ora_inizio'], (string) $row['ora_fine'], (string) $row['stato']);
    }
    public static function loadByChef(int $idChef): array
    {
        return static::fetchAllWhere('id_chef = :id AND data >= CURDATE() ORDER BY data ASC, ora_inizio ASC', ['id' => $idChef]);
    }

    public static function loadBySlot(int $idChef, string $data, string $oraInizio, string $oraFine): ?EDisponibilitaChef
    {
        return static::fetchOneWhere(
            'id_chef = :id_chef AND data = :data AND ora_inizio = :ora_inizio AND ora_fine = :ora_fine',
            ['id_chef' => $idChef, 'data' => $data, 'ora_inizio' => $oraInizio, 'ora_fine' => $oraFine]
        );
    }

    public static function verificaDisponibilita(int $idChef, string $data, string $oraInizio, string $oraFine): bool
    {
        return static::run('verifica disponibilita chef', static function () use ($idChef, $data, $oraInizio, $oraFine): bool {
            $sql = 'SELECT 1 FROM disponibilita_chef
                    WHERE id_chef = :id_chef AND data = :data AND ora_inizio = :ora_inizio
                      AND ora_fine = :ora_fine AND stato = :stato LIMIT 1';
            $statement = static::connection()->prepare($sql);
            $statement->execute(['id_chef' => $idChef, 'data' => $data, 'ora_inizio' => $oraInizio, 'ora_fine' => $oraFine, 'stato' => EDisponibilitaChef::STATO_LIBERA]);

            return $statement->fetchColumn() !== false;
        });
    }
}
