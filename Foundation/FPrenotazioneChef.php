<?php
declare(strict_types=1);

require_once __DIR__ . '/FBaseJoinPersistence.php';
require_once __DIR__ . '/FPrenotazione.php';
require_once __DIR__ . '/../Entity/EPrenotazioneChef.php';

class FPrenotazioneChef
{
    public static function exist(int $id): bool { return FBaseJoinPersistence::exists($id, 'prenotazioni_chef', 'id_prenotazione'); }
    public static function load(int $id): ?EPrenotazioneChef
    {
        return FBaseJoinPersistence::run('load prenotazione chef', static function () use ($id): ?EPrenotazioneChef {
            $sql = 'SELECT p.*, pc.id_chef, pc.id_menu, pc.indirizzo_servizio, pc.numero_persone, pc.richieste_speciali
                    FROM prenotazioni p INNER JOIN prenotazioni_chef pc ON pc.id_prenotazione = p.id_prenotazione
                    WHERE p.id_prenotazione = :id LIMIT 1';
            $statement = FBaseJoinPersistence::connection()->prepare($sql);
            $statement->execute(['id' => $id]);
            $row = $statement->fetch();
            return $row !== false ? self::hydrate($row) : null;
        });
    }
    public static function store(EPrenotazioneChef $p): bool|int
    {
        return FBaseJoinPersistence::run('store prenotazione chef', static function () use ($p): bool|int {
            $pdo = FBaseJoinPersistence::connection();
            $pdo->beginTransaction();
            try {
                $id = FBaseJoinPersistence::storePrenotazioneBase($p);
                $p->setIdPrenotazione($id);
                $sql = 'INSERT INTO prenotazioni_chef (id_prenotazione, id_chef, id_menu, indirizzo_servizio, numero_persone, richieste_speciali)
                        VALUES (:id_prenotazione, :id_chef, :id_menu, :indirizzo_servizio, :numero_persone, :richieste_speciali)';
                $pdo->prepare($sql)->execute(self::specialValues($p));
                $pdo->commit();
                return $id;
            } catch (Throwable $exception) {
                $pdo->rollBack();
                throw $exception;
            }
        });
    }
    public static function update(EPrenotazioneChef $p): bool
    {
        return FBaseJoinPersistence::run('update prenotazione chef', static function () use ($p): bool {
            if ($p->getIdPrenotazione() === null) { return false; }
            FBaseJoinPersistence::updatePrenotazioneBase($p);
            $sql = 'UPDATE prenotazioni_chef SET id_chef = :id_chef, id_menu = :id_menu,
                    indirizzo_servizio = :indirizzo_servizio, numero_persone = :numero_persone,
                    richieste_speciali = :richieste_speciali WHERE id_prenotazione = :id_prenotazione';
            return FBaseJoinPersistence::connection()->prepare($sql)->execute(self::specialValues($p));
        });
    }
    public static function delete(int $id): bool { return FPrenotazione::delete($id); }

    public static function loadRichieste(int $idChef): array
    {
        return FBaseJoinPersistence::run('load richieste prenotazione chef', static function () use ($idChef): array {
            $sql = 'SELECT p.*, pc.id_chef, pc.id_menu, pc.indirizzo_servizio, pc.numero_persone, pc.richieste_speciali
                    FROM prenotazioni p INNER JOIN prenotazioni_chef pc ON pc.id_prenotazione = p.id_prenotazione
                    WHERE pc.id_chef = :id_chef AND p.stato = :stato
                    ORDER BY p.data_servizio ASC, p.ora_inizio ASC';
            $statement = FBaseJoinPersistence::connection()->prepare($sql);
            $statement->execute(['id_chef' => $idChef, 'stato' => EPrenotazione::STATO_IN_ATTESA]);

            return array_map(static fn (array $row): EPrenotazioneChef => self::hydrate($row), $statement->fetchAll());
        });
    }

    public static function loadByRichiedente(int $idUtente): array
    {
        return FBaseJoinPersistence::run('load prenotazioni chef richiedente', static function () use ($idUtente): array {
            $sql = 'SELECT p.*, pc.id_chef, pc.id_menu, pc.indirizzo_servizio, pc.numero_persone, pc.richieste_speciali
                    FROM prenotazioni p INNER JOIN prenotazioni_chef pc ON pc.id_prenotazione = p.id_prenotazione
                    WHERE p.id_richiedente = :id_utente
                    ORDER BY p.data_servizio DESC, p.ora_inizio DESC';
            $statement = FBaseJoinPersistence::connection()->prepare($sql);
            $statement->execute(['id_utente' => $idUtente]);

            return array_map(static fn (array $row): EPrenotazioneChef => self::hydrate($row), $statement->fetchAll());
        });
    }

    public static function loadByChef(int $idChef): array
    {
        return FBaseJoinPersistence::run('load prenotazioni ricevute chef', static function () use ($idChef): array {
            $sql = 'SELECT p.*, pc.id_chef, pc.id_menu, pc.indirizzo_servizio, pc.numero_persone, pc.richieste_speciali
                    FROM prenotazioni p INNER JOIN prenotazioni_chef pc ON pc.id_prenotazione = p.id_prenotazione
                    WHERE pc.id_chef = :id_chef
                    ORDER BY FIELD(p.stato, :in_attesa, :accettata, :rifiutata, :pagata, :completata, :cancellata),
                             p.data_servizio ASC, p.ora_inizio ASC';
            $statement = FBaseJoinPersistence::connection()->prepare($sql);
            $statement->execute([
                'id_chef' => $idChef,
                'in_attesa' => EPrenotazione::STATO_IN_ATTESA,
                'accettata' => EPrenotazione::STATO_ACCETTATA,
                'rifiutata' => EPrenotazione::STATO_RIFIUTATA,
                'pagata' => EPrenotazione::STATO_PAGATA,
                'completata' => EPrenotazione::STATO_COMPLETATA,
                'cancellata' => EPrenotazione::STATO_CANCELLATA,
            ]);

            return array_map(static fn (array $row): EPrenotazioneChef => self::hydrate($row), $statement->fetchAll());
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

    private static function hydrate(array $row): EPrenotazioneChef
    {
        return new EPrenotazioneChef((int) $row['id_prenotazione'], (int) $row['id_richiedente'], (string) $row['data_creazione'], (string) $row['data_servizio'], (string) $row['ora_inizio'], (string) $row['ora_fine'], (string) $row['stato'], (float) $row['importo_totale'], (string) ($row['note'] ?? ''), (int) $row['id_chef'], (int) $row['id_menu'], (string) $row['indirizzo_servizio'], (int) $row['numero_persone'], (string) ($row['richieste_speciali'] ?? ''));
    }
    private static function specialValues(EPrenotazioneChef $p): array
    {
        return ['id_prenotazione' => $p->getIdPrenotazione(), 'id_chef' => $p->getIdChef(), 'id_menu' => $p->getIdMenu(), 'indirizzo_servizio' => $p->getIndirizzoServizio(), 'numero_persone' => $p->getNumeroPersone(), 'richieste_speciali' => $p->getRichiesteSpeciali() ?: null];
    }
}
