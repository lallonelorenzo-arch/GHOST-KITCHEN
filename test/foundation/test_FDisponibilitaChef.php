<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Foundation/FDisponibilitaChef.php';

echo "Test FDisponibilitaChef\n";
echo "=======================\n\n";

echo "exist(1)\n";
var_dump(FDisponibilitaChef::exist(1));

echo "\nload(1)\n";
print_r(FDisponibilitaChef::load(1));

echo "\nloadByChef(5)\n";
print_r(FDisponibilitaChef::loadByChef(5));

echo "\nverificaDisponibilita(5, '2026-06-20', '19:00:00', '23:00:00')\n";
var_dump(FDisponibilitaChef::verificaDisponibilita(5, '2026-06-20', '19:00:00', '23:00:00'));
