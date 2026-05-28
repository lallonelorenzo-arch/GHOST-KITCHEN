<?php
declare(strict_types=1);

require_once __DIR__ . '/FAbstractTable.php';
require_once __DIR__ . '/../Entity/EMedia.php';

class FMedia extends FAbstractTable
{
    protected static function tableName(): string { return 'media'; }
    protected static function primaryKey(): string { return 'id_media'; }
    protected static function columns(): array { return ['id_media', 'tipo_owner', 'id_owner', 'tipo_media', 'nome_file', 'path_file', 'mime_type', 'descrizione', 'data_caricamento', 'ordine', 'stato']; }
    protected static function idFromEntity(object $entity): ?int { return $entity->getIdMedia(); }
    protected static function valuesFromEntity(object $entity): array
    {
        return ['id_media' => $entity->getIdMedia(), 'tipo_owner' => $entity->getTipoOwner(), 'id_owner' => $entity->getIdOwner(), 'tipo_media' => $entity->getTipoMedia(), 'nome_file' => $entity->getNomeFile(), 'path_file' => $entity->getPathFile(), 'mime_type' => $entity->getMimeType(), 'descrizione' => $entity->getDescrizione(), 'data_caricamento' => $entity->getDataCaricamento(), 'ordine' => $entity->getOrdine(), 'stato' => $entity->getStato()];
    }
    protected static function hydrate(array $row): EMedia
    {
        return new EMedia((int) $row['id_media'], (string) $row['tipo_owner'], (int) $row['id_owner'], (string) $row['tipo_media'], (string) $row['nome_file'], (string) $row['path_file'], (string) $row['mime_type'], (string) ($row['descrizione'] ?? ''), (string) $row['data_caricamento'], (int) $row['ordine'], (string) $row['stato']);
    }
    public static function loadByOwner(string $tipoOwner, int $idOwner): array
    {
        return static::fetchAllWhere('tipo_owner = :tipo_owner AND id_owner = :id_owner', ['tipo_owner' => $tipoOwner, 'id_owner' => $idOwner]);
    }
    public static function getPrincipale(string $tipoOwner, int $idOwner): ?EMedia
    {
        $items = static::fetchAllWhere('tipo_owner = :tipo_owner AND id_owner = :id_owner AND stato = :stato ORDER BY ordine ASC LIMIT 1', ['tipo_owner' => $tipoOwner, 'id_owner' => $idOwner, 'stato' => EMedia::STATO_ATTIVO]);
        return $items[0] ?? null;
    }
}
