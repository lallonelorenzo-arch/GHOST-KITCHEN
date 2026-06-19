<?php
declare(strict_types=1);

require_once __DIR__ . '/FConnectionDB.php';

/**
 * @internal Helper applicativo per aggregazioni dashboard.
 */
class FStatisticheDashboard
{
    public static function getStatisticheDashboard(array $filtri): array
    {
        return [
            'filtriApplicati' => $filtri,
            'prenotazioni' => self::getStatistichePrenotazioni($filtri),
            'pagamenti' => self::getStatistichePagamenti($filtri),
            'recensioni' => self::getStatisticheRecensioni($filtri),
            'moderazione' => self::getStatisticheModerazione($filtri),
        ];
    }

    private static function getStatistichePrenotazioni(array $filtri): array
    {
        $pdo = self::connection();
        [$whereBase, $paramsBase] = self::prenotazioniWhere($filtri, 'p');
        $whereSql = $whereBase !== '' ? ' WHERE ' . $whereBase : '';

        $prenotazioniTotali = (int) self::scalar('SELECT COUNT(*) FROM prenotazioni p' . $whereSql, $paramsBase);

        [$whereChef, $paramsChef] = self::prenotazioniWhere($filtri, 'p', 'chef');
        $prenotazioniChef = (int) self::scalar(
            'SELECT COUNT(*) FROM prenotazioni p INNER JOIN prenotazioni_chef pc ON pc.id_prenotazione = p.id_prenotazione' . ($whereChef !== '' ? ' WHERE ' . $whereChef : ''),
            $paramsChef
        );

        [$whereGk, $paramsGk] = self::prenotazioniWhere($filtri, 'p', 'ghost_kitchen');
        $prenotazioniGhostKitchen = (int) self::scalar(
            'SELECT COUNT(*) FROM prenotazioni p INNER JOIN prenotazioni_ghost_kitchen pgk ON pgk.id_prenotazione = p.id_prenotazione' . ($whereGk !== '' ? ' WHERE ' . $whereGk : ''),
            $paramsGk
        );

        $chefPiuPrenotati = [];
        if (($filtri['tipoPrenotazione'] ?? 'tutte') !== 'ghost_kitchen') {
            $sql = 'SELECT c.id_utente AS idChef, CONCAT(u.nome, " ", u.cognome) AS nome, COUNT(*) AS prenotazioni
                    FROM prenotazioni p
                    INNER JOIN prenotazioni_chef pc ON pc.id_prenotazione = p.id_prenotazione
                    INNER JOIN chef c ON c.id_utente = pc.id_chef
                    INNER JOIN utenti u ON u.id_utente = c.id_utente'
                    . ($whereChef !== '' ? ' WHERE ' . $whereChef : '') .
                    ' GROUP BY c.id_utente, u.nome, u.cognome
                    ORDER BY prenotazioni DESC
                    LIMIT 5';
            $statement = $pdo->prepare($sql);
            $statement->execute($paramsChef);
            $chefPiuPrenotati = $statement->fetchAll();
        }

        $ghostKitchenPiuPrenotate = [];
        if (($filtri['tipoPrenotazione'] ?? 'tutte') !== 'chef') {
            $sql = 'SELECT gk.id_ghost_kitchen AS idGhostKitchen, gk.nome, COUNT(*) AS prenotazioni
                    FROM prenotazioni p
                    INNER JOIN prenotazioni_ghost_kitchen pgk ON pgk.id_prenotazione = p.id_prenotazione
                    INNER JOIN ghost_kitchen gk ON gk.id_ghost_kitchen = pgk.id_ghost_kitchen'
                    . ($whereGk !== '' ? ' WHERE ' . $whereGk : '') .
                    ' GROUP BY gk.id_ghost_kitchen, gk.nome
                    ORDER BY prenotazioni DESC
                    LIMIT 5';
            $statement = $pdo->prepare($sql);
            $statement->execute($paramsGk);
            $ghostKitchenPiuPrenotate = $statement->fetchAll();
        }

        return [
            'prenotazioniTotali' => $prenotazioniTotali,
            'prenotazioniChef' => $prenotazioniChef,
            'prenotazioniGhostKitchen' => $prenotazioniGhostKitchen,
            'chefPiuPrenotati' => $chefPiuPrenotati,
            'ghostKitchenPiuPrenotate' => $ghostKitchenPiuPrenotate,
        ];
    }

    private static function getStatistichePagamenti(array $filtri): array
    {
        [$where, $params] = self::prenotazioniWhere($filtri, 'p');
        $whereParts = ['pay.stato = :stato_completato'];
        $params += ['stato_completato' => 'completato'];
        if ($where !== '') {
            $whereParts[] = $where;
        }

        $volumePagamenti = (float) self::scalar(
            'SELECT COALESCE(SUM(pay.importo), 0)
             FROM pagamenti pay
             INNER JOIN prenotazioni p ON p.id_prenotazione = pay.id_prenotazione
             WHERE ' . implode(' AND ', $whereParts),
            $params
        );

        return ['volumePagamenti' => $volumePagamenti];
    }

    private static function getStatisticheRecensioni(array $filtri): array
    {
        [$where, $params] = self::recensioniWhere($filtri, 'r');
        $whereSql = $where !== '' ? ' WHERE ' . $where : '';

        $sql = 'SELECT c.id_utente AS idChef, CONCAT(u.nome, " ", u.cognome) AS nome, c.valutazione_media AS valutazioneMedia
                FROM chef c INNER JOIN utenti u ON u.id_utente = c.id_utente
                ORDER BY c.valutazione_media DESC
                LIMIT 1';
        $statement = self::connection()->prepare($sql);
        $statement->execute();

        return [
            'chefConValutazioneMigliore' => $statement->fetch() ?: null,
            'recensioniChef' => (($filtri['tipoPrenotazione'] ?? 'tutte') === 'ghost_kitchen') ? 0 : (int) self::scalar(
                'SELECT COUNT(*) FROM recensioni r INNER JOIN recensioni_chef rc ON rc.id_recensione = r.id_recensione' . $whereSql,
                $params
            ),
            'recensioniGhostKitchen' => (($filtri['tipoPrenotazione'] ?? 'tutte') === 'chef') ? 0 : (int) self::scalar(
                'SELECT COUNT(*) FROM recensioni r INNER JOIN recensioni_ghost_kitchen rgk ON rgk.id_recensione = r.id_recensione' . $whereSql,
                $params
            ),
        ];
    }

    private static function getStatisticheModerazione(array $filtri): array
    {
        return [
            'segnalazioniAperte' => (int) self::scalar('SELECT COUNT(*) FROM segnalazioni WHERE stato = :stato', ['stato' => 'aperta']),
            'certificazioniInAttesa' => (int) self::scalar('SELECT COUNT(*) FROM certificazioni WHERE stato = :stato', ['stato' => 'in_attesa']),
        ];
    }

    private static function prenotazioniWhere(array $filtri, string $alias, ?string $forcedType = null): array
    {
        $where = [];
        $params = [];

        if (($filtri['dataDa'] ?? '') !== '') {
            $where[] = $alias . '.data_servizio >= :dataDa';
            $params['dataDa'] = $filtri['dataDa'];
        }

        if (($filtri['dataA'] ?? '') !== '') {
            $where[] = $alias . '.data_servizio <= :dataA';
            $params['dataA'] = $filtri['dataA'];
        }

        $tipo = $forcedType ?? ($filtri['tipoPrenotazione'] ?? 'tutte');
        if ($tipo === 'chef') {
            $where[] = 'EXISTS (SELECT 1 FROM prenotazioni_chef pc_filter WHERE pc_filter.id_prenotazione = ' . $alias . '.id_prenotazione)';
        } elseif ($tipo === 'ghost_kitchen') {
            $where[] = 'EXISTS (SELECT 1 FROM prenotazioni_ghost_kitchen pgk_filter WHERE pgk_filter.id_prenotazione = ' . $alias . '.id_prenotazione)';
        }

        return [implode(' AND ', $where), $params];
    }

    private static function recensioniWhere(array $filtri, string $alias): array
    {
        $where = [];
        $params = [];

        if (($filtri['dataDa'] ?? '') !== '') {
            $where[] = 'DATE(' . $alias . '.data_recensione) >= :dataDa';
            $params['dataDa'] = $filtri['dataDa'];
        }

        if (($filtri['dataA'] ?? '') !== '') {
            $where[] = 'DATE(' . $alias . '.data_recensione) <= :dataA';
            $params['dataA'] = $filtri['dataA'];
        }

        return [implode(' AND ', $where), $params];
    }

    private static function scalar(string $sql, array $params = []): mixed
    {
        $statement = self::connection()->prepare($sql);
        $statement->execute($params);
        return $statement->fetchColumn();
    }

    private static function connection(): PDO
    {
        return FConnectionDB::getInstance()->getConnection();
    }
}
