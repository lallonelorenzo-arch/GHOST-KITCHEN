<?php
declare(strict_types=1);

require_once __DIR__ . '/FRolePersistence.php';
require_once __DIR__ . '/FUtente.php';
require_once __DIR__ . '/../Entity/EAmministratore.php';

class FAmministratore
{
    public static function exist(int $id): bool { return FUtente::exist($id) && in_array(EUtente::TIPO_ADMIN, FUtente::getRuoli($id), true); }
    public static function load(int $id): ?EAmministratore { $u = FUtente::load($id); return self::exist($id) && $u !== null ? new EAmministratore($u->getIdUtente(), $u->getNome(), $u->getCognome(), $u->getEmail(), $u->getPasswordHash(), $u->getTelefono(), $u->getStato()) : null; }
    public static function store(EAmministratore $admin): bool|int { return FRolePersistence::storeRole($admin, 'amministratori'); }
    public static function update(EAmministratore $admin): bool { return FUtente::update($admin); }
    public static function delete(int $id): bool { return FRolePersistence::deleteRole($id, 'amministratori'); }
}
