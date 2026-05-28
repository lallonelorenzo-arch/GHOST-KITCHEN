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
        $prenotazioniTotali = (int) self::scalar('SELECT COUNT(*) FROM prenotazioni');
        $prenotazioniChef = (int) self::scalar('SELECT COUNT(*) FROM prenotazioni_chef');
        $prenotazioniGhostKitchen = (int) self::scalar('SELECT COUNT(*) FROM prenotazioni_ghost_kitchen');

        $sql = 'SELECT gk.id_ghost_kitchen AS idGhostKitchen, gk.nome, COUNT(*) AS prenotazioni
                FROM prenotazioni_ghost_kitchen pgk
                INNER JOIN ghost_kitchen gk ON gk.id_ghost_kitchen = pgk.id_ghost_kitchen
                GROUP BY gk.id_ghost_kitchen, gk.nome
                ORDER BY prenotazioni DESC
                LIMIT 5';
        $statement = $pdo->prepare($sql);
        $statement->execute();

        return [
            'prenotazioniTotali' => $prenotazioniTotali,
            'prenotazioniChef' => $prenotazioniChef,
            'prenotazioniGhostKitchen' => $prenotazioniGhostKitchen,
            'ghostKitchenPiuPrenotate' => $statement->fetchAll(),
        ];
    }

    private static function getStatistichePagamenti(array $filtri): array
    {
        return [
            'volumePagamenti' => (float) self::scalar('SELECT COALESCE(SUM(importo), 0) FROM pagamenti WHERE stato IN (:stato_completato, :stato_rimborsato, :stato_parziale)', ['stato_completato' => 'completato', 'stato_rimborsato' => 'rimborsato', 'stato_parziale' => 'parzialmente_rimborsato']),
            'numeroRimborsi' => (int) self::scalar('SELECT COUNT(*) FROM rimborsi'),
            'volumeRimborsi' => (float) self::scalar('SELECT COALESCE(SUM(importo), 0) FROM rimborsi WHERE stato IN (:approvato, :eseguito)', ['approvato' => 'approvato', 'eseguito' => 'eseguito']),
        ];
    }

    private static function getStatisticheRecensioni(array $filtri): array
    {
        $sql = 'SELECT c.id_utente AS idChef, CONCAT(u.nome, " ", u.cognome) AS nome, c.valutazione_media AS valutazioneMedia
                FROM chef c INNER JOIN utenti u ON u.id_utente = c.id_utente
                ORDER BY c.valutazione_media DESC
                LIMIT 1';
        $statement = self::connection()->prepare($sql);
        $statement->execute();

        return [
            'chefConValutazioneMigliore' => $statement->fetch() ?: null,
            'recensioniChef' => (int) self::scalar('SELECT COUNT(*) FROM recensioni_chef'),
            'recensioniGhostKitchen' => (int) self::scalar('SELECT COUNT(*) FROM recensioni_ghost_kitchen'),
        ];
    }

    private static function getStatisticheModerazione(array $filtri): array
    {
        return [
            'segnalazioniAperte' => (int) self::scalar('SELECT COUNT(*) FROM segnalazioni WHERE stato = :stato', ['stato' => 'aperta']),
            'certificazioniInAttesa' => (int) self::scalar('SELECT COUNT(*) FROM certificazioni WHERE stato = :stato', ['stato' => 'in_attesa']),
        ];
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
