<?php
declare(strict_types=1);

require_once __DIR__ . '/FBaseJoinPersistence.php';
require_once __DIR__ . '/FRecensionePersistence.php';
require_once __DIR__ . '/FRecensioneChef.php';
require_once __DIR__ . '/FRecensioneGhostKitchen.php';
require_once __DIR__ . '/../Entity/ERecensione.php';

class FRecensione
{
    public static function exist(int $id): bool { return FBaseJoinPersistence::exists($id, 'recensioni', 'id_recensione'); }
    public static function loadBase(int $id): ?array { return FBaseJoinPersistence::loadBase($id, 'recensioni', 'id_recensione'); }
    public static function delete(int $id): bool { return FBaseJoinPersistence::deleteBase($id, 'recensioni', 'id_recensione'); }

    public static function load(int $id): ?ERecensione
    {
        $recensioneChef = FRecensioneChef::load($id);
        if ($recensioneChef !== null) {
            return $recensioneChef;
        }

        return FRecensioneGhostKitchen::load($id);
    }

    public static function update(ERecensione $recensione): bool
    {
        if ($recensione instanceof ERecensioneChef) {
            return FRecensioneChef::update($recensione);
        }
        if ($recensione instanceof ERecensioneGhostKitchen) {
            return FRecensioneGhostKitchen::update($recensione);
        }

        return FRecensionePersistence::updateBase($recensione);
    }

    public static function loadCatalogo(array $filtri = []): array
    {
        return FBaseJoinPersistence::run('load catalogo recensioni', static function () use ($filtri): array {
            [$where, $params] = self::catalogoWhere($filtri);
            $orderBy = self::catalogoOrderBy((string) ($filtri['ordinamento'] ?? 'recenti'));
            $sql = self::catalogoSql() . $where . ' ORDER BY ' . $orderBy . ' LIMIT 200';
            $statement = FBaseJoinPersistence::connection()->prepare($sql);
            $statement->execute($params);

            return array_map([self::class, 'normalizeCatalogoRow'], $statement->fetchAll());
        });
    }

    public static function loadByAutore(int $idAutore, array $filtri = []): array
    {
        $filtri['idAutore'] = $idAutore;
        return self::loadCatalogo($filtri);
    }

    public static function loadTipologieCucinaRecensite(): array
    {
        return FBaseJoinPersistence::run('load tipologie cucina recensite', static function (): array {
            $sql = "SELECT DISTINCT c.tipologia_cucina
                    FROM recensioni r
                    INNER JOIN recensioni_chef rc ON rc.id_recensione = r.id_recensione
                    INNER JOIN chef c ON c.id_utente = rc.id_chef
                    WHERE c.tipologia_cucina IS NOT NULL AND c.tipologia_cucina <> ''
                    ORDER BY c.tipologia_cucina ASC";
            $statement = FBaseJoinPersistence::connection()->query($sql);

            return array_values(array_filter(
                array_map(static fn (array $row): string => (string) ($row['tipologia_cucina'] ?? ''), $statement->fetchAll()),
                static fn (string $value): bool => trim($value) !== ''
            ));
        });
    }

    private static function catalogoSql(): string
    {
        return 'SELECT * FROM (
                    SELECT r.id_recensione, r.id_autore, r.punteggio, r.commento, r.data_recensione, r.stato,
                           "chef" AS tipo_target,
                           rc.id_chef AS id_target,
                           rc.id_prenotazione_chef AS id_prenotazione,
                           CONCAT(target.nome, " ", target.cognome) AS target_nome,
                           c.specializzazione AS target_dettaglio,
                           c.tipologia_cucina,
                           CONCAT(autore.nome, " ", autore.cognome) AS autore_nome,
                           autore.email AS autore_email
                    FROM recensioni r
                    INNER JOIN recensioni_chef rc ON rc.id_recensione = r.id_recensione
                    INNER JOIN chef c ON c.id_utente = rc.id_chef
                    INNER JOIN utenti target ON target.id_utente = rc.id_chef
                    INNER JOIN utenti autore ON autore.id_utente = r.id_autore
                    UNION ALL
                    SELECT r.id_recensione, r.id_autore, r.punteggio, r.commento, r.data_recensione, r.stato,
                           "ghost_kitchen" AS tipo_target,
                           rgk.id_ghost_kitchen AS id_target,
                           rgk.id_prenotazione_ghost_kitchen AS id_prenotazione,
                           gk.nome AS target_nome,
                           CONCAT(gk.citta, " - ", gk.indirizzo) AS target_dettaglio,
                           NULL AS tipologia_cucina,
                           CONCAT(autore.nome, " ", autore.cognome) AS autore_nome,
                           autore.email AS autore_email
                    FROM recensioni r
                    INNER JOIN recensioni_ghost_kitchen rgk ON rgk.id_recensione = r.id_recensione
                    INNER JOIN ghost_kitchen gk ON gk.id_ghost_kitchen = rgk.id_ghost_kitchen
                    INNER JOIN utenti autore ON autore.id_utente = r.id_autore
                ) recensioni_catalogo';
    }

    private static function catalogoWhere(array $filtri): array
    {
        $where = [];
        $params = [];

        $idAutore = (int) ($filtri['idAutore'] ?? 0);
        if ($idAutore > 0) {
            $where[] = 'id_autore = :id_autore';
            $params['id_autore'] = $idAutore;
        }

        $tipo = strtolower(trim((string) ($filtri['tipo'] ?? 'tutte')));
        if (in_array($tipo, ['chef', 'ghost_kitchen'], true)) {
            $where[] = 'tipo_target = :tipo_target';
            $params['tipo_target'] = $tipo;
        }

        $stato = strtolower(trim((string) ($filtri['stato'] ?? 'tutti')));
        if (in_array($stato, [ERecensione::STATO_VISIBILE, ERecensione::STATO_NASCOSTA, ERecensione::STATO_RIMOSSA], true)) {
            $where[] = 'stato = :stato';
            $params['stato'] = $stato;
        }

        $tipologiaCucina = strtolower(trim((string) ($filtri['tipologiaCucina'] ?? '')));
        if ($tipologiaCucina !== '') {
            $where[] = 'LOWER(tipologia_cucina) = :tipologia_cucina';
            $params['tipologia_cucina'] = $tipologiaCucina;
        }

        return [$where === [] ? '' : ' WHERE ' . implode(' AND ', $where), $params];
    }

    private static function catalogoOrderBy(string $ordinamento): string
    {
        return match (strtolower(trim($ordinamento))) {
            'valutazioni_alte' => 'punteggio DESC, data_recensione DESC, id_recensione DESC',
            'valutazioni_basse' => 'punteggio ASC, data_recensione DESC, id_recensione DESC',
            'cucina' => 'tipologia_cucina IS NULL ASC, tipologia_cucina ASC, data_recensione DESC, id_recensione DESC',
            default => 'data_recensione DESC, id_recensione DESC',
        };
    }

    private static function normalizeCatalogoRow(array $row): array
    {
        return [
            'idRecensione' => (int) $row['id_recensione'],
            'idAutore' => (int) $row['id_autore'],
            'autoreNome' => (string) ($row['autore_nome'] ?? ''),
            'autoreEmail' => (string) ($row['autore_email'] ?? ''),
            'punteggio' => (int) $row['punteggio'],
            'commento' => (string) ($row['commento'] ?? ''),
            'dataRecensione' => (string) $row['data_recensione'],
            'stato' => (string) $row['stato'],
            'tipoTarget' => (string) $row['tipo_target'],
            'idTarget' => (int) $row['id_target'],
            'idPrenotazione' => (int) $row['id_prenotazione'],
            'targetNome' => (string) ($row['target_nome'] ?? ''),
            'targetDettaglio' => (string) ($row['target_dettaglio'] ?? ''),
            'tipologiaCucina' => (string) ($row['tipologia_cucina'] ?? ''),
        ];
    }
}
