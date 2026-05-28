<?php
declare(strict_types=1);

require_once __DIR__ . '/FAbstractTable.php';
require_once __DIR__ . '/../Entity/EMenu.php';

class FMenu extends FAbstractTable
{
    protected static function tableName(): string { return 'menu'; }
    protected static function primaryKey(): string { return 'id_menu'; }
    protected static function columns(): array { return ['id_menu', 'id_chef', 'nome', 'descrizione', 'prezzo_persona', 'attivo']; }
    protected static function idFromEntity(object $entity): ?int { return $entity->getIdMenu(); }
    protected static function valuesFromEntity(object $entity): array
    {
        return ['id_menu' => $entity->getIdMenu(), 'id_chef' => $entity->getIdChef(), 'nome' => $entity->getNome(), 'descrizione' => $entity->getDescrizione(), 'prezzo_persona' => $entity->getPrezzoPersona(), 'attivo' => $entity->isAttivo() ? 1 : 0];
    }
    protected static function hydrate(array $row): EMenu
    {
        return new EMenu((int) $row['id_menu'], (int) $row['id_chef'], (string) $row['nome'], (string) $row['descrizione'], (float) $row['prezzo_persona'], (bool) $row['attivo']);
    }
    public static function loadByChef(int $idChef): array
    {
        return static::fetchAllWhere('id_chef = :id', ['id' => $idChef]);
    }
}
