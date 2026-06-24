<?php
declare(strict_types=1);

require_once __DIR__ . '/FBaseJoinPersistence.php';
require_once __DIR__ . '/../Entity/EPrenotazione.php';

// Mapper minimale della tabella base `prenotazioni`.
// Le prenotazioni concrete sono caricate da FPrenotazioneChef e FPrenotazioneGhostKitchen.
class FPrenotazione
{
    public static function exist(int $id): bool
    {
        // Controllo generico sulla tabella base, utile prima di operazioni su prenotazioni specifiche.
        return FBaseJoinPersistence::exists($id, 'prenotazioni', 'id_prenotazione');
    }

    public static function loadBase(int $id): ?array
    {
        // Restituisce i soli campi comuni; i mapper figli completano l'Entity concreta.
        return FBaseJoinPersistence::loadBase($id, 'prenotazioni', 'id_prenotazione');
    }

    public static function delete(int $id): bool
    {
        // La cancellazione parte dalla tabella base; i vincoli DB gestiscono eventuali record collegati.
        return FBaseJoinPersistence::deleteBase($id, 'prenotazioni', 'id_prenotazione');
    }
}
