<?php
declare(strict_types=1);

require_once __DIR__ . '/FAbstractTable.php';
require_once __DIR__ . '/../Entity/ECertificazione.php';

class FCertificazione extends FAbstractTable
{
    protected static function tableName(): string { return 'certificazioni'; }
    protected static function primaryKey(): string { return 'id_certificazione'; }
    protected static function columns(): array { return ['id_certificazione', 'id_chef', 'tipo', 'nome_file', 'path_file', 'stato', 'data_caricamento', 'data_validazione', 'note_admin']; }
    protected static function idFromEntity(object $entity): ?int { return $entity->getIdCertificazione(); }
    protected static function valuesFromEntity(object $entity): array
    {
        return ['id_certificazione' => $entity->getIdCertificazione(), 'id_chef' => $entity->getIdChef(), 'tipo' => $entity->getTipo(), 'nome_file' => $entity->getNomeFile(), 'path_file' => $entity->getPathFile(), 'stato' => $entity->getStato(), 'data_caricamento' => $entity->getDataCaricamento(), 'data_validazione' => $entity->getDataValidazione() ?: null, 'note_admin' => $entity->getNoteAdmin() ?: null];
    }
    protected static function hydrate(array $row): ECertificazione
    {
        return new ECertificazione((int) $row['id_certificazione'], (int) $row['id_chef'], (string) $row['tipo'], (string) $row['nome_file'], (string) $row['path_file'], (string) $row['stato'], (string) $row['data_caricamento'], (string) ($row['data_validazione'] ?? ''), (string) ($row['note_admin'] ?? ''));
    }
    public static function loadByChef(int $idChef): array
    {
        return static::fetchAllWhere('id_chef = :id', ['id' => $idChef]);
    }
    public static function loadByStato(string $stato): array
    {
        return static::fetchAllWhere('stato = :stato', ['stato' => $stato]);
    }
}
