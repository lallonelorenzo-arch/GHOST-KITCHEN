<?php
declare(strict_types=1);

require_once __DIR__ . '/FBaseJoinPersistence.php';
require_once __DIR__ . '/FRecensione.php';
require_once __DIR__ . '/FRecensionePersistence.php';
require_once __DIR__ . '/../Entity/ERecensioneGhostKitchen.php';

class FRecensioneGhostKitchen
{
    public static function exist(int $id): bool { return FBaseJoinPersistence::exists($id, 'recensioni_ghost_kitchen', 'id_recensione'); }
    public static function load(int $id): ?ERecensioneGhostKitchen
    {
        return FBaseJoinPersistence::run('load recensione ghost kitchen', static function () use ($id): ?ERecensioneGhostKitchen {
            $sql = 'SELECT r.*, rgk.id_ghost_kitchen, rgk.id_prenotazione_ghost_kitchen
                    FROM recensioni r INNER JOIN recensioni_ghost_kitchen rgk ON rgk.id_recensione = r.id_recensione
                    WHERE r.id_recensione = :id LIMIT 1';
            $statement = FBaseJoinPersistence::connection()->prepare($sql);
            $statement->execute(['id' => $id]);
            $row = $statement->fetch();
            return $row !== false ? new ERecensioneGhostKitchen((int) $row['id_recensione'], (int) $row['id_autore'], (int) $row['punteggio'], (string) ($row['commento'] ?? ''), (string) $row['data_recensione'], (string) $row['stato'], (int) $row['id_ghost_kitchen'], (int) $row['id_prenotazione_ghost_kitchen']) : null;
        });
    }
    public static function store(ERecensioneGhostKitchen $r): bool|int
    {
        return FBaseJoinPersistence::run('store recensione ghost kitchen', static function () use ($r): bool|int {
            $pdo = FBaseJoinPersistence::connection();
            $pdo->beginTransaction();
            try {
                $id = FRecensionePersistence::storeBase($r);
                $r->setIdRecensione($id);
                $sql = 'INSERT INTO recensioni_ghost_kitchen (id_recensione, id_ghost_kitchen, id_prenotazione_ghost_kitchen)
                        VALUES (:id_recensione, :id_ghost_kitchen, :id_prenotazione_ghost_kitchen)';
                $pdo->prepare($sql)->execute(['id_recensione' => $r->getIdRecensione(), 'id_ghost_kitchen' => $r->getIdGhostKitchen(), 'id_prenotazione_ghost_kitchen' => $r->getIdPrenotazioneGhostKitchen()]);
                $pdo->commit();
                return $id;
            } catch (Throwable $exception) {
                $pdo->rollBack();
                throw $exception;
            }
        });
    }
    public static function update(ERecensioneGhostKitchen $r): bool
    {
        if ($r->getIdRecensione() === null) { return false; }
        FRecensionePersistence::updateBase($r);
        $sql = 'UPDATE recensioni_ghost_kitchen SET id_ghost_kitchen = :id_ghost_kitchen, id_prenotazione_ghost_kitchen = :id_prenotazione_ghost_kitchen WHERE id_recensione = :id_recensione';
        return FBaseJoinPersistence::connection()->prepare($sql)->execute(['id_recensione' => $r->getIdRecensione(), 'id_ghost_kitchen' => $r->getIdGhostKitchen(), 'id_prenotazione_ghost_kitchen' => $r->getIdPrenotazioneGhostKitchen()]);
    }
    public static function delete(int $id): bool { return FRecensione::delete($id); }

    public static function loadByGhostKitchen(int $idGhostKitchen): array
    {
        return FBaseJoinPersistence::run('load recensioni ghost kitchen', static function () use ($idGhostKitchen): array {
            $sql = 'SELECT r.*, rgk.id_ghost_kitchen, rgk.id_prenotazione_ghost_kitchen
                    FROM recensioni r INNER JOIN recensioni_ghost_kitchen rgk ON rgk.id_recensione = r.id_recensione
                    WHERE rgk.id_ghost_kitchen = :id_ghost_kitchen
                    ORDER BY r.data_recensione DESC';
            $statement = FBaseJoinPersistence::connection()->prepare($sql);
            $statement->execute(['id_ghost_kitchen' => $idGhostKitchen]);

            $items = [];
            foreach ($statement->fetchAll() as $row) {
                $items[] = new ERecensioneGhostKitchen(
                    (int) $row['id_recensione'],
                    (int) $row['id_autore'],
                    (int) $row['punteggio'],
                    (string) ($row['commento'] ?? ''),
                    (string) $row['data_recensione'],
                    (string) $row['stato'],
                    (int) $row['id_ghost_kitchen'],
                    (int) $row['id_prenotazione_ghost_kitchen']
                );
            }

            return $items;
        });
    }

    public static function aggiornaValutazioneGhostKitchen(int $idGhostKitchen): array
    {
        return FBaseJoinPersistence::run('aggiorna valutazione ghost kitchen', static function () use ($idGhostKitchen): array {
            $sql = 'SELECT AVG(r.punteggio) AS media, COUNT(*) AS totale
                    FROM recensioni r INNER JOIN recensioni_ghost_kitchen rgk ON rgk.id_recensione = r.id_recensione
                    WHERE rgk.id_ghost_kitchen = :id_ghost_kitchen AND r.stato = :stato';
            $statement = FBaseJoinPersistence::connection()->prepare($sql);
            $statement->execute(['id_ghost_kitchen' => $idGhostKitchen, 'stato' => ERecensione::STATO_VISIBILE]);
            $row = $statement->fetch();
            $media = round((float) ($row['media'] ?? 0), 2);
            $totale = (int) ($row['totale'] ?? 0);

            $update = 'UPDATE ghost_kitchen SET valutazione_media = :media, numero_recensioni = :totale WHERE id_ghost_kitchen = :id_ghost_kitchen';
            FBaseJoinPersistence::connection()->prepare($update)->execute(['media' => $media, 'totale' => $totale, 'id_ghost_kitchen' => $idGhostKitchen]);

            return ['idGhostKitchen' => $idGhostKitchen, 'valutazioneMediaAggiornata' => $media, 'numeroRecensioni' => $totale];
        });
    }
}
