<?php
declare(strict_types=1);

require_once __DIR__ . '/FRolePersistence.php';
require_once __DIR__ . '/FUtente.php';
require_once __DIR__ . '/../Entity/ECliente.php';

class FCliente
{
    public static function exist(int $id): bool { return FUtente::exist($id) && in_array(EUtente::TIPO_CLIENTE, FUtente::getRuoli($id), true); }
    public static function load(int $id): ?ECliente { $u = FUtente::load($id); return self::exist($id) && $u !== null ? new ECliente($u->getIdUtente(), $u->getNome(), $u->getCognome(), $u->getEmail(), $u->getPasswordHash(), $u->getTelefono(), $u->getStato()) : null; }
    public static function store(ECliente $cliente): bool|int { return FRolePersistence::storeRole($cliente, 'clienti'); }
    public static function update(ECliente $cliente): bool { return FUtente::update($cliente); }
    public static function delete(int $id): bool { return FRolePersistence::deleteRole($id, 'clienti'); }
}
