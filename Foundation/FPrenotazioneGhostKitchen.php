<?php
declare(strict_types=1);

require_once __DIR__ . '/FBaseJoinPersistence.php';
require_once __DIR__ . '/FPrenotazione.php';
require_once __DIR__ . '/../Entity/EPrenotazioneGhostKitchen.php';

class FPrenotazioneGhostKitchen
{
    public static function exist(int $id): bool { return FBaseJoinPersistence::exists($id, 'prenotazioni_ghost_kitchen', 'id_prenotazione'); }
    public static function load(int $id): ?EPrenotazioneGhostKitchen
    {
        return FBaseJoinPersistence::run('load prenotazione ghost kitchen', static function () use ($id): ?EPrenotazioneGhostKitchen {
            $sql = 'SELECT p.*, pgk.id_ghost_kitchen, pgk.tipo_richiedente
                    FROM prenotazioni p INNER JOIN prenotazioni_ghost_kitchen pgk ON pgk.id_prenotazione = p.id_prenotazione
                    WHERE p.id_prenotazione = :id LIMIT 1';
            $statement = FBaseJoinPersistence::connection()->prepare($sql);
            $statement->execute(['id' => $id]);
            $row = $statement->fetch();
            return $row !== false ? self::hydrate($row) : null;
        });
    }
    public static function store(EPrenotazioneGhostKitchen $p): bool|int
    {
        return FBaseJoinPersistence::run('store prenotazione ghost kitchen', static function () use ($p): bool|int {
            $pdo = FBaseJoinPersistence::connection();
            $pdo->beginTransaction();
            try {
                $id = FBaseJoinPersistence::storePrenotazioneBase($p);
                $p->setIdPrenotazione($id);
                $sql = 'INSERT INTO prenotazioni_ghost_kitchen (id_prenotazione, id_ghost_kitchen, tipo_richiedente)
                        VALUES (:id_prenotazione, :id_ghost_kitchen, :tipo_richiedente)';
                $pdo->prepare($sql)->execute(['id_prenotazione' => $p->getIdPrenotazione(), 'id_ghost_kitchen' => $p->getIdGhostKitchen(), 'tipo_richiedente' => $p->getTipoRichiedente()]);
                $pdo->commit();
                return $id;
            } catch (Throwable $exception) {
                $pdo->rollBack();
                throw $exception;
            }
        });
    }
    public static function update(EPrenotazioneGhostKitchen $p): bool
    {
        return FBaseJoinPersistence::run('update prenotazione ghost kitchen', static function () use ($p): bool {
            if ($p->getIdPrenotazione() === null) { return false; }
            FBaseJoinPersistence::updatePrenotazioneBase($p);
            $sql = 'UPDATE prenotazioni_ghost_kitchen SET id_ghost_kitchen = :id_ghost_kitchen, tipo_richiedente = :tipo_richiedente WHERE id_prenotazione = :id_prenotazione';
            return FBaseJoinPersistence::connection()->prepare($sql)->execute(['id_prenotazione' => $p->getIdPrenotazione(), 'id_ghost_kitchen' => $p->getIdGhostKitchen(), 'tipo_richiedente' => $p->getTipoRichiedente()]);
        });
    }
    public static function delete(int $id): bool { return FPrenotazione::delete($id); }

    public static function loadRichiesteByGestore(int $idGestore): array
    {
        return FBaseJoinPersistence::run('load richieste prenotazione ghost kitchen', static function () use ($idGestore): array {
            $sql = 'SELECT p.*, pgk.id_ghost_kitchen, pgk.tipo_richiedente
                    FROM prenotazioni p
                    INNER JOIN prenotazioni_ghost_kitchen pgk ON pgk.id_prenotazione = p.id_prenotazione
                    INNER JOIN ghost_kitchen gk ON gk.id_ghost_kitchen = pgk.id_ghost_kitchen
                    WHERE gk.id_gestore = :id_gestore AND p.stato = :stato
                    ORDER BY p.data_servizio ASC, p.ora_inizio ASC';
            $statement = FBaseJoinPersistence::connection()->prepare($sql);
            $statement->execute(['id_gestore' => $idGestore, 'stato' => EPrenotazione::STATO_IN_ATTESA]);

            return array_map(static fn (array $row): EPrenotazioneGhostKitchen => self::hydrate($row), $statement->fetchAll());
        });
    }

    public static function loadByRichiedente(int $idUtente): array
    {
        return FBaseJoinPersistence::run('load prenotazioni ghost kitchen richiedente', static function () use ($idUtente): array {
            $sql = 'SELECT p.*, pgk.id_ghost_kitchen, pgk.tipo_richiedente
                    FROM prenotazioni p
                    INNER JOIN prenotazioni_ghost_kitchen pgk ON pgk.id_prenotazione = p.id_prenotazione
                    WHERE p.id_richiedente = :id_utente
                    ORDER BY p.data_servizio DESC, p.ora_inizio DESC';
            $statement = FBaseJoinPersistence::connection()->prepare($sql);
            $statement->execute(['id_utente' => $idUtente]);

            return array_map(static fn (array $row): EPrenotazioneGhostKitchen => self::hydrate($row), $statement->fetchAll());
        });
    }

    public static function verificaRecensibile(int $idPrenotazione, int $idAutore): array
    {
        $prenotazione = self::load($idPrenotazione);
        if ($prenotazione === null) {
            return ['recensibile' => false, 'motivo' => 'Prenotazione non trovata.'];
        }
        if ($prenotazione->getIdRichiedente() !== $idAutore) {
            return ['recensibile' => false, 'motivo' => 'Autore non associato alla prenotazione.'];
        }
        if ($prenotazione->getStato() !== EPrenotazione::STATO_COMPLETATA) {
            return ['recensibile' => false, 'motivo' => 'Prenotazione non completata.'];
        }

        return ['recensibile' => true, 'motivo' => 'Prenotazione completata e recensibile.'];
    }

    private static function hydrate(array $row): EPrenotazioneGhostKitchen
    {
        return new EPrenotazioneGhostKitchen((int) $row['id_prenotazione'], (int) $row['id_richiedente'], (string) $row['data_creazione'], (string) $row['data_servizio'], (string) $row['ora_inizio'], (string) $row['ora_fine'], (string) $row['stato'], (float) $row['importo_totale'], (string) ($row['note'] ?? ''), (int) $row['id_ghost_kitchen'], (string) $row['tipo_richiedente']);
    }
}
