<?php
declare(strict_types=1);

require_once __DIR__ . '/FBaseJoinPersistence.php';
require_once __DIR__ . '/../Entity/ERecensione.php';

/**
 * @internal Helper tecnico della Foundation. Non usare dai Control.
 */
class FRecensionePersistence
{
    public static function storeBase(ERecensione $r): int
    {
        $values = ['id_recensione' => $r->getIdRecensione(), 'id_autore' => $r->getIdAutore(), 'punteggio' => $r->getPunteggio(), 'commento' => $r->getCommento() ?: null, 'data_recensione' => $r->getDataRecensione(), 'stato' => $r->getStato()];
        if ($r->getIdRecensione() === null) { unset($values['id_recensione']); }
        $columns = array_keys($values);
        $sql = 'INSERT INTO recensioni (' . implode(', ', $columns) . ') VALUES (:' . implode(', :', $columns) . ')';
        FBaseJoinPersistence::connection()->prepare($sql)->execute($values);
        return $r->getIdRecensione() ?? (int) FBaseJoinPersistence::connection()->lastInsertId();
    }
    public static function updateBase(ERecensione $r): bool
    {
        $sql = 'UPDATE recensioni SET id_autore = :id_autore, punteggio = :punteggio, commento = :commento,
                data_recensione = :data_recensione, stato = :stato WHERE id_recensione = :id_recensione';
        return FBaseJoinPersistence::connection()->prepare($sql)->execute(['id_recensione' => $r->getIdRecensione(), 'id_autore' => $r->getIdAutore(), 'punteggio' => $r->getPunteggio(), 'commento' => $r->getCommento() ?: null, 'data_recensione' => $r->getDataRecensione(), 'stato' => $r->getStato()]);
    }
}
