<?php
declare(strict_types=1);

require_once __DIR__ . '/FAbstractTable.php';
require_once __DIR__ . '/../Entity/EAttrezzatura.php';

class FAttrezzatura extends FAbstractTable
{
    protected static function tableName(): string { return 'attrezzature'; }
    protected static function primaryKey(): string { return 'id_attrezzatura'; }
    protected static function columns(): array { return ['id_attrezzatura', 'id_ghost_kitchen', 'nome', 'categoria', 'descrizione', 'quantita']; }
    protected static function idFromEntity(object $entity): ?int { return $entity->getIdAttrezzatura(); }
    protected static function valuesFromEntity(object $entity): array
    {
        return ['id_attrezzatura' => $entity->getIdAttrezzatura(), 'id_ghost_kitchen' => $entity->getIdGhostKitchen(), 'nome' => $entity->getNome(), 'categoria' => $entity->getCategoria(), 'descrizione' => $entity->getDescrizione(), 'quantita' => $entity->getQuantita()];
    }
    protected static function hydrate(array $row): EAttrezzatura
    {
        return new EAttrezzatura((int) $row['id_attrezzatura'], (int) $row['id_ghost_kitchen'], (string) $row['nome'], (string) $row['categoria'], (string) ($row['descrizione'] ?? ''), (int) $row['quantita']);
    }
    public static function loadByGhostKitchen(int $idGhostKitchen): array
    {
        return static::fetchAllWhere('id_ghost_kitchen = :id', ['id' => $idGhostKitchen]);
    }
}
