<?php
declare(strict_types=1);

require_once __DIR__ . '/FAbstractTable.php';
require_once __DIR__ . '/FPrenotazioneChef.php';
require_once __DIR__ . '/FPrenotazioneGhostKitchen.php';
require_once __DIR__ . '/../Entity/EPagamento.php';

class FPagamento extends FAbstractTable
{
    protected static function tableName(): string { return 'pagamenti'; }
    protected static function primaryKey(): string { return 'id_pagamento'; }
    protected static function columns(): array { return ['id_pagamento', 'id_prenotazione', 'importo', 'stato', 'codice_transazione', 'data_pagamento']; }
    protected static function idFromEntity(object $entity): ?int { return $entity->getIdPagamento(); }
    protected static function valuesFromEntity(object $entity): array
    {
        $values = ['id_pagamento' => $entity->getIdPagamento(), 'id_prenotazione' => $entity->getIdPrenotazione(), 'importo' => $entity->getImporto(), 'stato' => $entity->getStato(), 'codice_transazione' => $entity->getCodiceTransazione() ?: null, 'data_pagamento' => $entity->getDataPagamento() ?: null];
        if (self::hasColumn('tipo_pagamento')) {
            $values['tipo_pagamento'] = 'totale';
        }

        return $values;
    }
    protected static function hydrate(array $row): EPagamento
    {
        return new EPagamento((int) $row['id_pagamento'], (int) $row['id_prenotazione'], self::tipoPrenotazione((int) $row['id_prenotazione']), (float) $row['importo'], (string) $row['stato'], (string) ($row['codice_transazione'] ?? ''), (string) ($row['data_pagamento'] ?? ''));
    }
    private static function tipoPrenotazione(int $idPrenotazione): string
    {
        if (FPrenotazioneGhostKitchen::exist($idPrenotazione)) { return EPagamento::PRENOTAZIONE_GHOST_KITCHEN; }
        return EPagamento::PRENOTAZIONE_CHEF;
    }

    public static function loadByPrenotazione(string $tipoPrenotazione, int $idPrenotazione): ?EPagamento
    {
        $pagamenti = static::fetchAllWhere(
            'id_prenotazione = :id AND stato = :stato ORDER BY id_pagamento DESC LIMIT 1',
            ['id' => $idPrenotazione, 'stato' => EPagamento::STATO_COMPLETATO]
        );
        return $pagamenti[0] ?? null;
    }

    public static function loadByUtente(int $idUtente): array
    {
        return static::run('caricamento pagamenti utente', static function () use ($idUtente): array {
            $sql = 'SELECT pg.id_pagamento,
                           pg.id_prenotazione,
                           pg.importo,
                           pg.stato,
                           pg.codice_transazione,
                           pg.data_pagamento
                    FROM pagamenti pg
                    INNER JOIN prenotazioni p ON p.id_prenotazione = pg.id_prenotazione
                    WHERE p.id_richiedente = :id_utente
                      AND pg.stato = :stato
                    ORDER BY COALESCE(pg.data_pagamento, p.data_creazione) DESC, pg.id_pagamento DESC';
            $statement = static::connection()->prepare($sql);
            $statement->execute([
                'id_utente' => $idUtente,
                'stato' => EPagamento::STATO_COMPLETATO,
            ]);

            return array_map(static fn (array $row): EPagamento => static::hydrate($row), $statement->fetchAll());
        });
    }

    public static function calcolaImporto(string $tipoPrenotazione, int $idPrenotazione): float
    {
        $prenotazione = $tipoPrenotazione === EPagamento::PRENOTAZIONE_CHEF
            ? FPrenotazioneChef::load($idPrenotazione)
            : FPrenotazioneGhostKitchen::load($idPrenotazione);

        if ($prenotazione === null) {
            throw new InvalidArgumentException('Prenotazione non trovata per calcolo importo.');
        }

        return $prenotazione->getImportoTotale();
    }

    private static function hasColumn(string $column): bool
    {
        static $cache = [];
        if (array_key_exists($column, $cache)) {
            return $cache[$column];
        }

        $sql = "SELECT 1
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'pagamenti'
                  AND COLUMN_NAME = :column
                LIMIT 1";
        $statement = static::connection()->prepare($sql);
        $statement->execute(['column' => $column]);
        $cache[$column] = $statement->fetchColumn() !== false;

        return $cache[$column];
    }
}
