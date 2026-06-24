<?php
declare(strict_types=1);

require_once __DIR__ . '/FConnectionDB.php';
require_once __DIR__ . '/../Entity/EPrenotazione.php';

/**
 * @internal Helper tecnico della Foundation. Non usare dai Control.
 *
 * Centralizza le operazioni comuni alle entita con tabella base + tabella figlia.
 * Nel progetto e usato soprattutto per le prenotazioni:
 * `prenotazioni` contiene i campi comuni, mentre le tabelle specializzate
 * aggiungono i dettagli chef o ghost kitchen.
 */
class FBaseJoinPersistence
{
    public static function connection(): PDO { return FConnectionDB::getInstance()->getConnection(); }

    // Verifica esistenza di un record su una tabella/pk indicata dal mapper chiamante.
    public static function exists(int $id, string $table, string $pk): bool
    {
        return self::run('exist', static function () use ($id, $table, $pk): bool {
            $statement = self::connection()->prepare(sprintf('SELECT 1 FROM %s WHERE %s = :id LIMIT 1', $table, $pk));
            $statement->execute(['id' => $id]);
            return $statement->fetchColumn() !== false;
        });
    }

    // Carica una riga grezza della tabella base; la hydrate concreta resta nei mapper specifici.
    public static function loadBase(int $id, string $table, string $pk): ?array
    {
        return self::run('load base', static function () use ($id, $table, $pk): ?array {
            $statement = self::connection()->prepare(sprintf('SELECT * FROM %s WHERE %s = :id LIMIT 1', $table, $pk));
            $statement->execute(['id' => $id]);
            $row = $statement->fetch();
            return $row !== false ? $row : null;
        });
    }

    // Cancella dalla tabella base: le FK con cascade gestiscono le righe figlie quando previste.
    public static function deleteBase(int $id, string $table, string $pk): bool
    {
        return self::run('delete base', static function () use ($id, $table, $pk): bool {
            $statement = self::connection()->prepare(sprintf('DELETE FROM %s WHERE %s = :id', $table, $pk));
            $statement->execute(['id' => $id]);
            return $statement->rowCount() > 0;
        });
    }

    // Inserisce i campi comuni di EPrenotazione nella tabella `prenotazioni`.
    public static function storePrenotazioneBase(EPrenotazione $p): int
    {
        $values = self::prenotazioneValues($p);
        if ($p->getIdPrenotazione() === null) {
            unset($values['id_prenotazione']);
        }
        $columns = array_keys($values);
        $sql = 'INSERT INTO prenotazioni (' . implode(', ', $columns) . ') VALUES (:' . implode(', :', $columns) . ')';
        self::connection()->prepare($sql)->execute($values);
        return $p->getIdPrenotazione() ?? (int) self::connection()->lastInsertId();
    }

    // Aggiorna solo i campi comuni; i campi specifici sono aggiornati dai mapper figli.
    public static function updatePrenotazioneBase(EPrenotazione $p): bool
    {
        $values = self::prenotazioneValues($p);
        $id = $values['id_prenotazione'];
        unset($values['id_prenotazione']);
        $sql = 'UPDATE prenotazioni SET id_richiedente = :id_richiedente, data_creazione = :data_creazione,
                data_servizio = :data_servizio, ora_inizio = :ora_inizio, ora_fine = :ora_fine,
                stato = :stato, importo_totale = :importo_totale, note = :note WHERE id_prenotazione = :__id';
        $values['__id'] = $id;
        return self::connection()->prepare($sql)->execute($values);
    }

    // Traduce l'Entity base nei nomi colonna usati dallo schema SQL.
    private static function prenotazioneValues(EPrenotazione $p): array
    {
        return ['id_prenotazione' => $p->getIdPrenotazione(), 'id_richiedente' => $p->getIdRichiedente(), 'data_creazione' => $p->getDataCreazione(), 'data_servizio' => $p->getDataServizio(), 'ora_inizio' => $p->getOraInizio(), 'ora_fine' => $p->getOraFine(), 'stato' => $p->getStato(), 'importo_totale' => $p->getImportoTotale(), 'note' => $p->getNote() ?: null];
    }

    // Wrapper errori: trasforma PDOException in messaggi Foundation uniformi.
    public static function run(string $operation, callable $callback): mixed
    {
        try { return $callback(); } catch (PDOException $exception) { throw new RuntimeException('Errore Foundation durante: ' . $operation . '.', 0, $exception); }
    }
}
