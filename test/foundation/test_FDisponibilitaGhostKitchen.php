<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Foundation/FDisponibilitaGhostKitchen.php';

echo "Test FDisponibilitaGhostKitchen\n";
echo "===============================\n\n";

echo "exist(1)\n";
var_dump(FDisponibilitaGhostKitchen::exist(1));

echo "\nload(1)\n";
print_r(FDisponibilitaGhostKitchen::load(1));

echo "\nloadByGhostKitchen(1)\n";
print_r(FDisponibilitaGhostKitchen::loadByGhostKitchen(1));

echo "\nverificaDisponibilita(1, '2026-06-20', '09:00:00', '13:00:00')\n";
var_dump(FDisponibilitaGhostKitchen::verificaDisponibilita(1, '2026-06-20', '09:00:00', '13:00:00'));
