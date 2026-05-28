<?php
declare(strict_types=1);

require_once __DIR__ . '/FAbstractTable.php';
require_once __DIR__ . '/../Entity/ESegnalazione.php';
require_once __DIR__ . '/FUtente.php';
require_once __DIR__ . '/FChef.php';
require_once __DIR__ . '/FGhostKitchen.php';
require_once __DIR__ . '/FRecensione.php';
require_once __DIR__ . '/FMenu.php';

class FSegnalazione extends FAbstractTable
{
    protected static function tableName(): string { return 'segnalazioni'; }
    protected static function primaryKey(): string { return 'id_segnalazione'; }
    protected static function columns(): array { return ['id_segnalazione', 'id_segnalante', 'tipo_target', 'id_target', 'motivo', 'descrizione', 'stato', 'data_segnalazione', 'data_gestione', 'note_admin']; }
    protected static function idFromEntity(object $entity): ?int { return $entity->getIdSegnalazione(); }
    protected static function valuesFromEntity(object $entity): array
    {
        return ['id_segnalazione' => $entity->getIdSegnalazione(), 'id_segnalante' => $entity->getIdSegnalante(), 'tipo_target' => $entity->getTipoTarget(), 'id_target' => $entity->getIdTarget(), 'motivo' => $entity->getMotivo() ?: null, 'descrizione' => $entity->getDescrizione() ?: null, 'stato' => $entity->getStato(), 'data_segnalazione' => $entity->getDataSegnalazione(), 'data_gestione' => $entity->getDataGestione() ?: null, 'note_admin' => $entity->getNoteAdmin() ?: null];
    }
    protected static function hydrate(array $row): ESegnalazione
    {
        return new ESegnalazione((int) $row['id_segnalazione'], (int) $row['id_segnalante'], (string) $row['tipo_target'], (int) $row['id_target'], (string) ($row['motivo'] ?? ''), (string) ($row['descrizione'] ?? ''), (string) $row['stato'], (string) $row['data_segnalazione'], (string) ($row['data_gestione'] ?? ''), (string) ($row['note_admin'] ?? ''));
    }
    public static function loadByStato(string $stato): array
    {
        return static::fetchAllWhere('stato = :stato', ['stato' => $stato]);
    }

    public static function loadTarget(string $tipoTarget, int $idTarget): mixed
    {
        return match (strtolower(trim($tipoTarget))) {
            ESegnalazione::TARGET_UTENTE => FUtente::load($idTarget),
            ESegnalazione::TARGET_CHEF => FChef::load($idTarget),
            ESegnalazione::TARGET_GHOST_KITCHEN => FGhostKitchen::load($idTarget),
            ESegnalazione::TARGET_RECENSIONE => FRecensione::load($idTarget),
            ESegnalazione::TARGET_MENU => FMenu::load($idTarget),
            default => null,
        };
    }
}
