<?php
declare(strict_types=1);

require_once __DIR__ . '/FRolePersistence.php';
require_once __DIR__ . '/FUtente.php';
require_once __DIR__ . '/../Entity/EChef.php';

class FChef
{
    public static function exist(int $id): bool
    {
        return FRolePersistence::existsInRoleTable($id, 'chef');
    }

    public static function load(int $id): ?EChef
    {
        return FRolePersistence::run('load chef', static function () use ($id): ?EChef {
            $sql = 'SELECT u.id_utente, u.nome, u.cognome, u.email, u.password_hash, u.telefono, u.stato,
                           c.biografia, c.specializzazione, c.tipologia_cucina, c.prezzo_base,
                           c.anni_esperienza, c.stato_verifica, c.valutazione_media, c.numero_recensioni
                    FROM utenti u INNER JOIN chef c ON c.id_utente = u.id_utente
                    WHERE u.id_utente = :id LIMIT 1';
            $statement = FRolePersistence::connection()->prepare($sql);
            $statement->execute(['id' => $id]);
            $row = $statement->fetch();
            return $row !== false ? self::hydrate($row) : null;
        });
    }

    public static function store(EChef $chef): bool|int
    {
        return FRolePersistence::run('store chef', static function () use ($chef): bool|int {
            $id = $chef->getIdChef();
            if ($id === null) {
                $id = (int) FUtente::store($chef);
                $chef->setIdChef($id);
            } else {
                FUtente::update($chef);
            }

            $sql = 'INSERT INTO chef (id_utente, biografia, specializzazione, tipologia_cucina, prezzo_base, anni_esperienza, stato_verifica, valutazione_media, numero_recensioni)
                    VALUES (:id_utente, :biografia, :specializzazione, :tipologia_cucina, :prezzo_base, :anni_esperienza, :stato_verifica, :valutazione_media, :numero_recensioni)';
            $statement = FRolePersistence::connection()->prepare($sql);
            $statement->execute(self::roleValues($chef));
            return $id;
        });
    }

    public static function update(EChef $chef): bool
    {
        return FRolePersistence::run('update chef', static function () use ($chef): bool {
            if ($chef->getIdChef() === null) { return false; }
            FUtente::update($chef);
            $sql = 'UPDATE chef SET biografia = :biografia, specializzazione = :specializzazione,
                    tipologia_cucina = :tipologia_cucina, prezzo_base = :prezzo_base,
                    anni_esperienza = :anni_esperienza, stato_verifica = :stato_verifica,
                    valutazione_media = :valutazione_media, numero_recensioni = :numero_recensioni
                    WHERE id_utente = :id_utente';
            return FRolePersistence::connection()->prepare($sql)->execute(self::roleValues($chef));
        });
    }

    public static function delete(int $id): bool
    {
        return FRolePersistence::deleteRole($id, 'chef');
    }

    public static function search(string $localita, string $tipologiaCucina, float $budgetMax, int $valutazioneMin): array
    {
        return FRolePersistence::run('search chef', static function () use ($localita, $tipologiaCucina, $budgetMax, $valutazioneMin): array {
            // TODO: lo schema non contiene una localita dello chef; il filtro localita e ignorato finche non sara modellato.
            $where = ['u.stato = :stato'];
            $params = ['stato' => EUtente::STATO_ATTIVO];

            $tipologiaCucina = strtolower(trim($tipologiaCucina));
            if ($tipologiaCucina !== '') {
                $where[] = '(LOWER(c.tipologia_cucina) = :tipologia OR LOWER(c.specializzazione) LIKE :specializzazione)';
                $params['tipologia'] = $tipologiaCucina;
                $params['specializzazione'] = '%' . $tipologiaCucina . '%';
            }
            if ($budgetMax > 0) {
                $where[] = 'c.prezzo_base <= :budget';
                $params['budget'] = $budgetMax;
            }
            if ($valutazioneMin > 0) {
                $where[] = 'c.valutazione_media >= :valutazione';
                $params['valutazione'] = $valutazioneMin;
            }

            $sql = 'SELECT u.id_utente, u.nome, u.cognome, u.email, u.password_hash, u.telefono, u.stato,
                           c.biografia, c.specializzazione, c.tipologia_cucina, c.prezzo_base,
                           c.anni_esperienza, c.stato_verifica, c.valutazione_media, c.numero_recensioni
                    FROM utenti u INNER JOIN chef c ON c.id_utente = u.id_utente
                    WHERE ' . implode(' AND ', $where) . '
                    ORDER BY c.valutazione_media DESC, c.prezzo_base ASC';
            $statement = FRolePersistence::connection()->prepare($sql);
            $statement->execute($params);

            return array_map(static fn (array $row): EChef => self::hydrate($row), $statement->fetchAll());
        });
    }

    private static function hydrate(array $row): EChef
    {
        return new EChef((int) $row['id_utente'], (string) $row['nome'], (string) $row['cognome'], (string) $row['email'], (string) $row['password_hash'], (string) $row['telefono'], (string) $row['stato'], (string) ($row['biografia'] ?? ''), (string) ($row['specializzazione'] ?? ''), (string) ($row['tipologia_cucina'] ?? ''), (float) ($row['prezzo_base'] ?? 0), (int) $row['anni_esperienza'], (string) $row['stato_verifica'], (float) $row['valutazione_media'], (int) $row['numero_recensioni']);
    }

    private static function roleValues(EChef $chef): array
    {
        return ['id_utente' => $chef->getIdChef(), 'biografia' => $chef->getBiografia() ?: null, 'specializzazione' => $chef->getSpecializzazione() ?: null, 'tipologia_cucina' => $chef->getTipologiaCucina() ?: null, 'prezzo_base' => $chef->getPrezzoBase(), 'anni_esperienza' => $chef->getAnniEsperienza(), 'stato_verifica' => $chef->getStatoVerifica(), 'valutazione_media' => $chef->getValutazioneMedia(), 'numero_recensioni' => $chef->getNumeroRecensioni()];
    }
}
