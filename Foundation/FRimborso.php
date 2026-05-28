<?php
declare(strict_types=1);

require_once __DIR__ . '/FAbstractTable.php';
require_once __DIR__ . '/../Entity/ERimborso.php';

class FRimborso extends FAbstractTable
{
    protected static function tableName(): string { return 'rimborsi'; }
    protected static function primaryKey(): string { return 'id_rimborso'; }
    protected static function columns(): array { return ['id_rimborso', 'id_pagamento', 'id_cancellazione', 'importo', 'motivo', 'stato', 'data_richiesta', 'data_esecuzione']; }
    protected static function idFromEntity(object $entity): ?int { return $entity->getIdRimborso(); }
    protected static function valuesFromEntity(object $entity): array
    {
        return ['id_rimborso' => $entity->getIdRimborso(), 'id_pagamento' => $entity->getIdPagamento(), 'id_cancellazione' => $entity->getIdCancellazione(), 'importo' => $entity->getImporto(), 'motivo' => $entity->getMotivo() ?: null, 'stato' => $entity->getStato(), 'data_richiesta' => $entity->getDataRichiesta(), 'data_esecuzione' => $entity->getDataEsecuzione() ?: null];
    }
    protected static function hydrate(array $row): ERimborso
    {
        return new ERimborso((int) $row['id_rimborso'], (int) $row['id_pagamento'], (int) $row['id_cancellazione'], (float) $row['importo'], (string) ($row['motivo'] ?? ''), (string) $row['stato'], (string) $row['data_richiesta'], (string) ($row['data_esecuzione'] ?? ''));
    }
}
