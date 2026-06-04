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
    protected static function columns(): array { return ['id_pagamento', 'id_prenotazione', 'id_metodo_pagamento', 'importo', 'tipo_pagamento', 'stato', 'codice_transazione', 'data_pagamento']; }
    protected static function idFromEntity(object $entity): ?int { return $entity->getIdPagamento(); }
    protected static function valuesFromEntity(object $entity): array
    {
        return ['id_pagamento' => $entity->getIdPagamento(), 'id_prenotazione' => $entity->getIdPrenotazione(), 'id_metodo_pagamento' => $entity->getIdMetodoPagamento(), 'importo' => $entity->getImporto(), 'tipo_pagamento' => $entity->getTipoPagamento(), 'stato' => $entity->getStato(), 'codice_transazione' => $entity->getCodiceTransazione() ?: null, 'data_pagamento' => $entity->getDataPagamento() ?: null];
    }
    protected static function hydrate(array $row): EPagamento
    {
        return new EPagamento((int) $row['id_pagamento'], (int) $row['id_prenotazione'], self::tipoPrenotazione((int) $row['id_prenotazione']), isset($row['id_metodo_pagamento']) ? (int) $row['id_metodo_pagamento'] : null, (float) $row['importo'], (string) $row['tipo_pagamento'], (string) $row['stato'], (string) ($row['codice_transazione'] ?? ''), (string) ($row['data_pagamento'] ?? ''));
    }
    private static function tipoPrenotazione(int $idPrenotazione): string
    {
        if (FPrenotazioneGhostKitchen::exist($idPrenotazione)) { return EPagamento::PRENOTAZIONE_GHOST_KITCHEN; }
        return EPagamento::PRENOTAZIONE_CHEF;
    }

    public static function loadByPrenotazione(string $tipoPrenotazione, int $idPrenotazione): ?EPagamento
    {
        $pagamenti = static::fetchAllWhere('id_prenotazione = :id ORDER BY id_pagamento DESC LIMIT 1', ['id' => $idPrenotazione]);
        return $pagamenti[0] ?? null;
    }

    public static function loadByUtente(int $idUtente): array
    {
        return static::run('caricamento pagamenti utente', static function () use ($idUtente): array {
            $sql = 'SELECT pg.*
                    FROM pagamenti pg
                    INNER JOIN prenotazioni p ON p.id_prenotazione = pg.id_prenotazione
                    WHERE p.id_richiedente = :id_utente
                    ORDER BY COALESCE(pg.data_pagamento, p.data_creazione) DESC, pg.id_pagamento DESC';
            $statement = static::connection()->prepare($sql);
            $statement->execute(['id_utente' => $idUtente]);

            return array_map(static fn (array $row): EPagamento => static::hydrate($row), $statement->fetchAll());
        });
    }

    public static function calcolaImporto(string $tipoPrenotazione, int $idPrenotazione, string $tipoPagamento): float
    {
        $prenotazione = $tipoPrenotazione === EPagamento::PRENOTAZIONE_CHEF
            ? FPrenotazioneChef::load($idPrenotazione)
            : FPrenotazioneGhostKitchen::load($idPrenotazione);

        if ($prenotazione === null) {
            throw new InvalidArgumentException('Prenotazione non trovata per calcolo importo.');
        }

        $totale = $prenotazione->getImportoTotale();
        return match (strtolower(trim($tipoPagamento))) {
            EPagamento::TIPO_CAPARRA => round($totale * 0.20, 2),
            EPagamento::TIPO_SALDO => round($totale * 0.80, 2),
            EPagamento::TIPO_PENALE => round($totale * 0.10, 2),
            default => $totale,
        };
    }
}
