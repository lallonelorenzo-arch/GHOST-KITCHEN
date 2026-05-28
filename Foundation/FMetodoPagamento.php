<?php
declare(strict_types=1);

require_once __DIR__ . '/FAbstractTable.php';
require_once __DIR__ . '/../Entity/EMetodoPagamento.php';

class FMetodoPagamento extends FAbstractTable
{
    protected static function tableName(): string { return 'metodi_pagamento'; }
    protected static function primaryKey(): string { return 'id_metodo_pagamento'; }
    protected static function columns(): array { return ['id_metodo_pagamento', 'id_utente', 'tipo', 'intestatario', 'circuito', 'ultime_quattro_cifre', 'scadenza_mese', 'scadenza_anno', 'attivo']; }
    protected static function idFromEntity(object $entity): ?int { return $entity->getIdMetodoPagamento(); }
    protected static function valuesFromEntity(object $entity): array
    {
        return ['id_metodo_pagamento' => $entity->getIdMetodoPagamento(), 'id_utente' => $entity->getIdUtente(), 'tipo' => $entity->getTipo(), 'intestatario' => $entity->getIntestatario(), 'circuito' => $entity->getCircuito() ?: null, 'ultime_quattro_cifre' => $entity->getUltimeQuattroCifre() ?: null, 'scadenza_mese' => $entity->getScadenzaMese() ?: null, 'scadenza_anno' => $entity->getScadenzaAnno() ?: null, 'attivo' => $entity->isAttivo() ? 1 : 0];
    }
    protected static function hydrate(array $row): EMetodoPagamento
    {
        return new EMetodoPagamento((int) $row['id_metodo_pagamento'], (int) $row['id_utente'], (string) $row['tipo'], (string) $row['intestatario'], (string) ($row['circuito'] ?? ''), (string) ($row['ultime_quattro_cifre'] ?? ''), (int) ($row['scadenza_mese'] ?? 0), (int) ($row['scadenza_anno'] ?? 0), (bool) $row['attivo']);
    }
    public static function loadByUtente(int $idUtente): array
    {
        return static::fetchAllWhere('id_utente = :id AND attivo = 1', ['id' => $idUtente]);
    }
}
