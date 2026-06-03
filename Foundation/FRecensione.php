<?php
declare(strict_types=1);

require_once __DIR__ . '/FBaseJoinPersistence.php';
require_once __DIR__ . '/FRecensionePersistence.php';
require_once __DIR__ . '/FRecensioneChef.php';
require_once __DIR__ . '/FRecensioneGhostKitchen.php';
require_once __DIR__ . '/../Entity/ERecensione.php';

class FRecensione
{
    public static function exist(int $id): bool { return FBaseJoinPersistence::exists($id, 'recensioni', 'id_recensione'); }
    public static function loadBase(int $id): ?array { return FBaseJoinPersistence::loadBase($id, 'recensioni', 'id_recensione'); }
    public static function delete(int $id): bool { return FBaseJoinPersistence::deleteBase($id, 'recensioni', 'id_recensione'); }

    public static function load(int $id): ?ERecensione
    {
        $recensioneChef = FRecensioneChef::load($id);
        if ($recensioneChef !== null) {
            return $recensioneChef;
        }

        return FRecensioneGhostKitchen::load($id);
    }

    public static function update(ERecensione $recensione): bool
    {
        if ($recensione instanceof ERecensioneChef) {
            return FRecensioneChef::update($recensione);
        }
        if ($recensione instanceof ERecensioneGhostKitchen) {
            return FRecensioneGhostKitchen::update($recensione);
        }

        return FRecensionePersistence::updateBase($recensione);
    }
}
