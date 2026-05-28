<?php
declare(strict_types=1);

require_once __DIR__ . '/FBaseJoinPersistence.php';
require_once __DIR__ . '/../Entity/EPrenotazione.php';

class FPrenotazione
{
    public static function exist(int $id): bool
    {
        return FBaseJoinPersistence::exists($id, 'prenotazioni', 'id_prenotazione');
    }

    public static function loadBase(int $id): ?array
    {
        return FBaseJoinPersistence::loadBase($id, 'prenotazioni', 'id_prenotazione');
    }

    public static function delete(int $id): bool
    {
        return FBaseJoinPersistence::deleteBase($id, 'prenotazioni', 'id_prenotazione');
    }
}
