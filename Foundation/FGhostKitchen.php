<?php
declare(strict_types=1);

require_once __DIR__ . '/FAbstractTable.php';
require_once __DIR__ . '/../Entity/EGhostKitchen.php';
require_once __DIR__ . '/../Entity/EGestore.php';

class FGhostKitchen extends FAbstractTable
{
    protected static function tableName(): string { return 'ghost_kitchen'; }
    protected static function primaryKey(): string { return 'id_ghost_kitchen'; }
    protected static function columns(): array { return ['id_ghost_kitchen', 'id_gestore', 'nome', 'descrizione', 'indirizzo', 'citta', 'cap', 'prezzo_orario', 'capienza', 'mq', 'stato', 'valutazione_media', 'numero_recensioni']; }
    protected static function idFromEntity(object $entity): ?int { return $entity->getId(); }
    protected static function valuesFromEntity(object $entity): array
    {
        return [
            'id_ghost_kitchen' => $entity->getId(),
            'id_gestore' => $entity->getIdGestore(),
            'nome' => $entity->getNome(),
            'descrizione' => $entity->getDescrizione(),
            'indirizzo' => $entity->getIndirizzo(),
            'citta' => $entity->getCitta(),
            'cap' => $entity->getCap(),
            'prezzo_orario' => $entity->getPrezzoOrario(),
            'capienza' => $entity->getCapienza(),
            'mq' => $entity->getMq(),
            'stato' => $entity->getStato(),
            'valutazione_media' => $entity->getValutazioneMedia(),
            'numero_recensioni' => $entity->getNumeroRecensioni(),
        ];
    }
    protected static function hydrate(array $row): EGhostKitchen
    {
        return new EGhostKitchen((int) $row['id_ghost_kitchen'], (int) $row['id_gestore'], (string) $row['nome'], (string) $row['descrizione'], (string) $row['indirizzo'], (string) $row['citta'], (string) $row['cap'], (float) $row['prezzo_orario'], (int) $row['capienza'], (float) $row['mq'], (string) $row['stato'], (float) $row['valutazione_media'], (int) $row['numero_recensioni']);
    }

    public static function loadByGestore(int $idGestore): array
    {
        if ($idGestore <= 0) {
            return [];
        }

        return static::run('caricamento ghost kitchen per gestore', static function () use ($idGestore): array {
            $statement = static::connection()->prepare(
                'SELECT * FROM ghost_kitchen WHERE id_gestore = :id_gestore ORDER BY nome ASC'
            );
            $statement->execute(['id_gestore' => $idGestore]);

            return array_map(static fn (array $row): EGhostKitchen => static::hydrate($row), $statement->fetchAll());
        });
    }

    public static function search(string $localita, float $budgetMax, int $valutazioneMin): array
    {
        return static::run('ricerca ghost kitchen', static function () use ($localita, $budgetMax, $valutazioneMin): array {
            $where = ['gk.stato = :stato', 'gestori.stato_verifica = :stato_verifica_gestore'];
            $params = ['stato' => EGhostKitchen::STATO_ATTIVA, 'stato_verifica_gestore' => EGestore::STATO_VERIFICA_VERIFICATO];

            $localita = strtolower(trim($localita));
            if ($localita !== '') {
                $where[] = 'LOWER(gk.citta) = :localita';
                $params['localita'] = $localita;
            }
            if ($budgetMax > 0) {
                $where[] = 'gk.prezzo_orario <= :budget';
                $params['budget'] = $budgetMax;
            }
            if ($valutazioneMin > 0) {
                $where[] = 'gk.valutazione_media >= :valutazione';
                $params['valutazione'] = $valutazioneMin;
            }

            $sql = 'SELECT gk.* FROM ghost_kitchen gk INNER JOIN gestori ON gestori.id_utente = gk.id_gestore WHERE ' . implode(' AND ', $where) . ' ORDER BY gk.valutazione_media DESC, gk.prezzo_orario ASC';
            $statement = static::connection()->prepare($sql);
            $statement->execute($params);

            return array_map(static fn (array $row): EGhostKitchen => static::hydrate($row), $statement->fetchAll());
        });
    }
}
