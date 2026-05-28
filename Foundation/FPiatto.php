<?php
declare(strict_types=1);

require_once __DIR__ . '/FAbstractTable.php';
require_once __DIR__ . '/../Entity/EPiatto.php';

class FPiatto extends FAbstractTable
{
    protected static function tableName(): string { return 'piatti'; }
    protected static function primaryKey(): string { return 'id_piatto'; }
    protected static function columns(): array { return ['id_piatto', 'id_menu', 'nome', 'categoria', 'descrizione', 'ingredienti', 'allergeni', 'prezzo_supplemento', 'ordine_visualizzazione']; }
    protected static function idFromEntity(object $entity): ?int { return $entity->getIdPiatto(); }
    protected static function valuesFromEntity(object $entity): array
    {
        return ['id_piatto' => $entity->getIdPiatto(), 'id_menu' => $entity->getIdMenu(), 'nome' => $entity->getNome(), 'categoria' => $entity->getCategoria(), 'descrizione' => $entity->getDescrizione(), 'ingredienti' => $entity->getIngredienti(), 'allergeni' => $entity->getAllergeni(), 'prezzo_supplemento' => $entity->getPrezzoSupplemento(), 'ordine_visualizzazione' => $entity->getOrdineVisualizzazione()];
    }
    protected static function hydrate(array $row): EPiatto
    {
        return new EPiatto((int) $row['id_piatto'], (int) $row['id_menu'], (string) $row['nome'], (string) $row['categoria'], (string) ($row['descrizione'] ?? ''), (string) ($row['ingredienti'] ?? ''), (string) ($row['allergeni'] ?? ''), (float) $row['prezzo_supplemento'], (int) $row['ordine_visualizzazione']);
    }
    public static function loadByMenu(int $idMenu): array
    {
        return static::fetchAllWhere('id_menu = :id', ['id' => $idMenu]);
    }
}
