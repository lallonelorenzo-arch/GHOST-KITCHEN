<?php
declare(strict_types=1);

require_once __DIR__ . '/FConnectionDB.php';
require_once __DIR__ . '/FUtente.php';
require_once __DIR__ . '/FCertificazione.php';
require_once __DIR__ . '/../Entity/EUtente.php';
require_once __DIR__ . '/../Entity/ECertificazione.php';
require_once __DIR__ . '/../Entity/EChef.php';
require_once __DIR__ . '/../Entity/EGestore.php';
require_once __DIR__ . '/../Entity/EGhostKitchen.php';

/**
 * @internal Servizio Foundation per creare un account multi-ruolo in transazione.
 */
class FRegistrazione
{
    public static function registra(EUtente $utente, array $ruoli, array $chefData = [], array $certificazioni = [], array $ghostKitchenData = []): int|false
    {
        $ruoli = self::normalizzaRuoli($ruoli);
        if ($ruoli === []) {
            return false;
        }

        $connection = FConnectionDB::getInstance()->getConnection();

        try {
            $connection->beginTransaction();

            $idUtente = FUtente::store($utente);
            if (!is_int($idUtente) || $idUtente <= 0) {
                throw new RuntimeException('Creazione utente non riuscita.');
            }
            $utente->setIdUtente($idUtente);

            if (in_array(EUtente::TIPO_CLIENTE, $ruoli, true)) {
                self::insertCliente($connection, $idUtente);
            }

            if (in_array(EUtente::TIPO_CHEF, $ruoli, true)) {
                self::insertChef($connection, $idUtente, $chefData);
                self::storeCertificazioniChef($idUtente, $certificazioni);
            }

            if (in_array(EUtente::TIPO_GESTORE, $ruoli, true)) {
                self::insertGestore($connection, $idUtente);
                if ($ghostKitchenData !== []) {
                    self::insertGhostKitchen($connection, $idUtente, $ghostKitchenData);
                }
            }

            $connection->commit();
            return $idUtente;
        } catch (Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }
            throw new RuntimeException('Errore Foundation durante la registrazione utente.', 0, $exception);
        }
    }

    private static function insertCliente(PDO $connection, int $idUtente): void
    {
        $statement = $connection->prepare('INSERT INTO clienti (id_utente) VALUES (:id_utente)');
        $statement->execute(['id_utente' => $idUtente]);
    }

    private static function insertGestore(PDO $connection, int $idUtente): void
    {
        $statement = $connection->prepare('INSERT INTO gestori (id_utente, stato_verifica) VALUES (:id_utente, :stato_verifica)');
        $statement->execute([
            'id_utente' => $idUtente,
            'stato_verifica' => EGestore::STATO_VERIFICA_IN_ATTESA,
        ]);
    }

    private static function insertChef(PDO $connection, int $idUtente, array $chefData): void
    {
        $statement = $connection->prepare(
            'INSERT INTO chef (id_utente, biografia, specializzazione, tipologia_cucina, prezzo_base, anni_esperienza, stato_verifica, valutazione_media, numero_recensioni)
             VALUES (:id_utente, :biografia, :specializzazione, :tipologia_cucina, :prezzo_base, :anni_esperienza, :stato_verifica, 0.00, 0)'
        );
        $statement->execute([
            'id_utente' => $idUtente,
            'biografia' => trim((string) ($chefData['biografia'] ?? '')) ?: null,
            'specializzazione' => trim((string) ($chefData['specializzazione'] ?? '')) ?: null,
            'tipologia_cucina' => trim((string) ($chefData['tipologiaCucina'] ?? '')) ?: null,
            'prezzo_base' => max(0, (float) ($chefData['prezzoBase'] ?? 0)),
            'anni_esperienza' => max(0, (int) ($chefData['anniEsperienza'] ?? 0)),
            'stato_verifica' => EChef::STATO_VERIFICA_IN_ATTESA,
        ]);
    }

    private static function insertGhostKitchen(PDO $connection, int $idGestore, array $ghostKitchenData): void
    {
        $statement = $connection->prepare(
            'INSERT INTO ghost_kitchen (id_gestore, nome, descrizione, indirizzo, citta, cap, prezzo_orario, capienza, mq, stato, valutazione_media, numero_recensioni)
             VALUES (:id_gestore, :nome, :descrizione, :indirizzo, :citta, :cap, :prezzo_orario, :capienza, :mq, :stato, 0.00, 0)'
        );
        $statement->execute([
            'id_gestore' => $idGestore,
            'nome' => trim((string) ($ghostKitchenData['nome'] ?? '')),
            'descrizione' => trim((string) ($ghostKitchenData['descrizione'] ?? '')),
            'indirizzo' => trim((string) ($ghostKitchenData['indirizzo'] ?? '')),
            'citta' => trim((string) ($ghostKitchenData['citta'] ?? '')),
            'cap' => trim((string) ($ghostKitchenData['cap'] ?? '')),
            'prezzo_orario' => max(0, (float) ($ghostKitchenData['prezzoOrario'] ?? 0)),
            'capienza' => max(1, (int) ($ghostKitchenData['capienza'] ?? 1)),
            'mq' => max(1, (float) ($ghostKitchenData['mq'] ?? 1)),
            'stato' => EGhostKitchen::STATO_SOSPESA,
        ]);
    }

    private static function storeCertificazioniChef(int $idChef, array $certificazioni): void
    {
        foreach ($certificazioni as $certificazione) {
            if (!$certificazione instanceof ECertificazione) {
                continue;
            }

            $certificazione->setIdOwner($idChef);
            $certificazione->setIdChef($idChef);
            $certificazione->setTipoOwner(ECertificazione::OWNER_CHEF);
            FCertificazione::store($certificazione);
        }
    }

    private static function normalizzaRuoli(array $ruoli): array
    {
        $ammessi = [EUtente::TIPO_CLIENTE, EUtente::TIPO_CHEF, EUtente::TIPO_GESTORE];
        $normalizzati = [];
        foreach ($ruoli as $ruolo) {
            $ruolo = strtolower(trim((string) $ruolo));
            if (in_array($ruolo, $ammessi, true) && !in_array($ruolo, $normalizzati, true)) {
                $normalizzati[] = $ruolo;
            }
        }

        return $normalizzati;
    }
}
