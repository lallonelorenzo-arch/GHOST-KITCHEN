<?php
declare(strict_types=1);

require_once __DIR__ . '/FRolePersistence.php';
require_once __DIR__ . '/FUtente.php';
require_once __DIR__ . '/../Entity/EGestore.php';

class FGestore
{
    public static function exist(int $id): bool { return FUtente::exist($id) && in_array(EUtente::TIPO_GESTORE, FUtente::getRuoli($id), true); }
    public static function load(int $id): ?EGestore { $u = FUtente::load($id); return self::exist($id) && $u !== null ? new EGestore($u->getIdUtente(), $u->getNome(), $u->getCognome(), $u->getEmail(), $u->getPasswordHash(), $u->getTelefono(), $u->getStato()) : null; }
    public static function store(EGestore $gestore): bool|int { return FRolePersistence::storeRole($gestore, 'gestori'); }
    public static function update(EGestore $gestore): bool { return FUtente::update($gestore); }
    public static function delete(int $id): bool { return FRolePersistence::deleteRole($id, 'gestori'); }
}
