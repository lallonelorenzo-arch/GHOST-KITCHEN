<?php
declare(strict_types=1);

require_once __DIR__ . '/FBaseJoinPersistence.php';
require_once __DIR__ . '/FPrenotazione.php';
require_once __DIR__ . '/../Entity/EPrenotazioneChef.php';

class FPrenotazioneChef
{
    public static function exist(int $id): bool
    {
        return FBaseJoinPersistence::exists($id, 'prenotazioni_chef', 'id_prenotazione');
    }

    public static function load(int $id): ?EPrenotazioneChef
    {
        return FBaseJoinPersistence::run('load prenotazione chef', static function () use ($id): ?EPrenotazioneChef {
            $statement = FBaseJoinPersistence::connection()->prepare(
                'SELECT p.*, pc.id_chef, pc.id_menu, pc.indirizzo_servizio, pc.numero_persone,
                        pc.richieste_speciali, pc.abbinamento_vini
                 FROM prenotazioni p
                 INNER JOIN prenotazioni_chef pc ON pc.id_prenotazione = p.id_prenotazione
                 WHERE p.id_prenotazione = :id
                 LIMIT 1'
            );
            $statement->execute(['id' => $id]);
            $row = $statement->fetch();

            return $row !== false ? self::hydrate($row) : null;
        });
    }

    public static function store(EPrenotazioneChef $prenotazione): bool|int
    {
        return FBaseJoinPersistence::run('store prenotazione chef', static function () use ($prenotazione): bool|int {
            $pdo = FBaseJoinPersistence::connection();
            $pdo->beginTransaction();
            try {
                $id = FBaseJoinPersistence::storePrenotazioneBase($prenotazione);
                $prenotazione->setIdPrenotazione($id);
                $statement = $pdo->prepare(
                    'INSERT INTO prenotazioni_chef (
                        id_prenotazione, id_chef, id_menu, indirizzo_servizio,
                        numero_persone, richieste_speciali, abbinamento_vini
                     ) VALUES (
                        :id_prenotazione, :id_chef, :id_menu, :indirizzo_servizio,
                        :numero_persone, :richieste_speciali, :abbinamento_vini
                     )'
                );
                $statement->execute(self::specialValues($prenotazione));
                $pdo->commit();

                return $id;
            } catch (Throwable $exception) {
                $pdo->rollBack();
                throw $exception;
            }
        });
    }

    public static function update(EPrenotazioneChef $prenotazione): bool
    {
        return FBaseJoinPersistence::run('update prenotazione chef', static function () use ($prenotazione): bool {
            if ($prenotazione->getIdPrenotazione() === null) {
                return false;
            }

            FBaseJoinPersistence::updatePrenotazioneBase($prenotazione);
            $statement = FBaseJoinPersistence::connection()->prepare(
                'UPDATE prenotazioni_chef
                 SET id_chef = :id_chef,
                     id_menu = :id_menu,
                     indirizzo_servizio = :indirizzo_servizio,
                     numero_persone = :numero_persone,
                     richieste_speciali = :richieste_speciali,
                     abbinamento_vini = :abbinamento_vini
                 WHERE id_prenotazione = :id_prenotazione'
            );

            return $statement->execute(self::specialValues($prenotazione));
        });
    }

    public static function delete(int $id): bool
    {
        return FPrenotazione::delete($id);
    }

    public static function loadRichieste(int $idChef): array
    {
        return FBaseJoinPersistence::run('load richieste prenotazione chef', static function () use ($idChef): array {
            $statement = FBaseJoinPersistence::connection()->prepare(
                self::selectSql() . '
                 WHERE pc.id_chef = :id_chef AND p.stato = :stato AND p.data_servizio >= CURDATE()
                 ORDER BY p.data_servizio ASC, p.ora_inizio ASC'
            );
            $statement->execute([
                'id_chef' => $idChef,
                'stato' => EPrenotazione::STATO_IN_ATTESA,
            ]);

            return array_map([self::class, 'hydrate'], $statement->fetchAll());
        });
    }

    public static function loadByRichiedente(int $idUtente): array
    {
        return FBaseJoinPersistence::run('load prenotazioni chef richiedente', static function () use ($idUtente): array {
            $statement = FBaseJoinPersistence::connection()->prepare(
                self::selectSql() . '
                 WHERE p.id_richiedente = :id_utente
                 ORDER BY p.data_servizio DESC, p.ora_inizio DESC'
            );
            $statement->execute(['id_utente' => $idUtente]);

            return array_map([self::class, 'hydrate'], $statement->fetchAll());
        });
    }

    public static function loadByChef(int $idChef): array
    {
        return FBaseJoinPersistence::run('load prenotazioni ricevute chef', static function () use ($idChef): array {
            $statement = FBaseJoinPersistence::connection()->prepare(
                self::selectSql() . '
                 WHERE pc.id_chef = :id_chef AND p.data_servizio >= CURDATE()
                 ORDER BY FIELD(p.stato, :in_attesa, :accettata, :rifiutata, :pagata, :completata),
                          p.data_servizio ASC, p.ora_inizio ASC'
            );
            $statement->execute([
                'id_chef' => $idChef,
                'in_attesa' => EPrenotazione::STATO_IN_ATTESA,
                'accettata' => EPrenotazione::STATO_ACCETTATA,
                'rifiutata' => EPrenotazione::STATO_RIFIUTATA,
                'pagata' => EPrenotazione::STATO_PAGATA,
                'completata' => EPrenotazione::STATO_COMPLETATA,
            ]);

            return array_map([self::class, 'hydrate'], $statement->fetchAll());
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
        $statement = FBaseJoinPersistence::connection()->prepare(
            'SELECT 1 FROM recensioni_chef WHERE id_prenotazione_chef = :id_prenotazione LIMIT 1'
        );
        $statement->execute(['id_prenotazione' => $idPrenotazione]);
        if ($statement->fetchColumn() !== false) {
            return ['recensibile' => false, 'motivo' => 'Hai gia pubblicato una recensione per questa prenotazione.'];
        }

        return ['recensibile' => true, 'motivo' => 'Prenotazione completata e recensibile.'];
    }

    private static function selectSql(): string
    {
        return 'SELECT p.*, pc.id_chef, pc.id_menu, pc.indirizzo_servizio, pc.numero_persone,
                       pc.richieste_speciali, pc.abbinamento_vini
                FROM prenotazioni p
                INNER JOIN prenotazioni_chef pc ON pc.id_prenotazione = p.id_prenotazione';
    }

    private static function hydrate(array $row): EPrenotazioneChef
    {
        return new EPrenotazioneChef(
            (int) $row['id_prenotazione'],
            (int) $row['id_richiedente'],
            (string) $row['data_creazione'],
            (string) $row['data_servizio'],
            (string) $row['ora_inizio'],
            (string) $row['ora_fine'],
            (string) $row['stato'],
            (float) $row['importo_totale'],
            (string) ($row['note'] ?? ''),
            (int) $row['id_chef'],
            (int) $row['id_menu'],
            (string) $row['indirizzo_servizio'],
            (int) $row['numero_persone'],
            (string) ($row['richieste_speciali'] ?? ''),
            (bool) ($row['abbinamento_vini'] ?? false)
        );
    }

    private static function specialValues(EPrenotazioneChef $prenotazione): array
    {
        return [
            'id_prenotazione' => $prenotazione->getIdPrenotazione(),
            'id_chef' => $prenotazione->getIdChef(),
            'id_menu' => $prenotazione->getIdMenu(),
            'indirizzo_servizio' => $prenotazione->getIndirizzoServizio(),
            'numero_persone' => $prenotazione->getNumeroPersone(),
            'richieste_speciali' => $prenotazione->getRichiesteSpeciali() ?: null,
            'abbinamento_vini' => $prenotazione->hasAbbinamentoVini() ? 1 : 0,
        ];
    }
}
