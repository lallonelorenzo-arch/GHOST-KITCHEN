<?php
declare(strict_types=1);

require_once __DIR__ . '/FBaseJoinPersistence.php';
require_once __DIR__ . '/FRecensione.php';
require_once __DIR__ . '/FRecensionePersistence.php';
require_once __DIR__ . '/../Entity/ERecensioneChef.php';

class FRecensioneChef
{
    public static function exist(int $id): bool { return FBaseJoinPersistence::exists($id, 'recensioni_chef', 'id_recensione'); }
    public static function load(int $id): ?ERecensioneChef
    {
        return FBaseJoinPersistence::run('load recensione chef', static function () use ($id): ?ERecensioneChef {
            $sql = 'SELECT r.*, rc.id_chef, rc.id_prenotazione_chef
                    FROM recensioni r INNER JOIN recensioni_chef rc ON rc.id_recensione = r.id_recensione
                    WHERE r.id_recensione = :id LIMIT 1';
            $statement = FBaseJoinPersistence::connection()->prepare($sql);
            $statement->execute(['id' => $id]);
            $row = $statement->fetch();
            return $row !== false ? self::hydrate($row) : null;
        });
    }
    public static function store(ERecensioneChef $r): bool|int
    {
        return FBaseJoinPersistence::run('store recensione chef', static function () use ($r): bool|int {
            $pdo = FBaseJoinPersistence::connection();
            $pdo->beginTransaction();
            try {
                $id = FRecensionePersistence::storeBase($r);
                $r->setIdRecensione($id);
                $sql = 'INSERT INTO recensioni_chef (id_recensione, id_chef, id_prenotazione_chef)
                        VALUES (:id_recensione, :id_chef, :id_prenotazione_chef)';
                $pdo->prepare($sql)->execute(['id_recensione' => $r->getIdRecensione(), 'id_chef' => $r->getIdChef(), 'id_prenotazione_chef' => $r->getIdPrenotazioneChef()]);
                $pdo->commit();
                return $id;
            } catch (Throwable $exception) {
                $pdo->rollBack();
                throw $exception;
            }
        });
    }
    public static function update(ERecensioneChef $r): bool
    {
        if ($r->getIdRecensione() === null) { return false; }
        FRecensionePersistence::updateBase($r);
        $sql = 'UPDATE recensioni_chef SET id_chef = :id_chef, id_prenotazione_chef = :id_prenotazione_chef WHERE id_recensione = :id_recensione';
        return FBaseJoinPersistence::connection()->prepare($sql)->execute(['id_recensione' => $r->getIdRecensione(), 'id_chef' => $r->getIdChef(), 'id_prenotazione_chef' => $r->getIdPrenotazioneChef()]);
    }
    public static function delete(int $id): bool { return FRecensione::delete($id); }

    public static function aggiornaValutazioneChef(int $idChef): array
    {
        return FBaseJoinPersistence::run('aggiorna valutazione chef', static function () use ($idChef): array {
            $sql = 'SELECT AVG(r.punteggio) AS media, COUNT(*) AS totale
                    FROM recensioni r INNER JOIN recensioni_chef rc ON rc.id_recensione = r.id_recensione
                    WHERE rc.id_chef = :id_chef AND r.stato = :stato';
            $statement = FBaseJoinPersistence::connection()->prepare($sql);
            $statement->execute(['id_chef' => $idChef, 'stato' => ERecensione::STATO_VISIBILE]);
            $row = $statement->fetch();
            $media = round((float) ($row['media'] ?? 0), 2);
            $totale = (int) ($row['totale'] ?? 0);

            $update = 'UPDATE chef SET valutazione_media = :media, numero_recensioni = :totale WHERE id_utente = :id_chef';
            FBaseJoinPersistence::connection()->prepare($update)->execute(['media' => $media, 'totale' => $totale, 'id_chef' => $idChef]);

            return ['idChef' => $idChef, 'valutazioneMediaAggiornata' => $media, 'numeroRecensioni' => $totale];
        });
    }

    private static function hydrate(array $row): ERecensioneChef
    {
        return new ERecensioneChef((int) $row['id_recensione'], (int) $row['id_autore'], (int) $row['punteggio'], (string) ($row['commento'] ?? ''), (string) $row['data_recensione'], (string) $row['stato'], (int) $row['id_chef'], (int) $row['id_prenotazione_chef']);
    }
}
