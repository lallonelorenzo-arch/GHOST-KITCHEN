<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Foundation/FAttrezzatura.php';

echo "Test FAttrezzatura\n";
echo "==================\n\n";

echo "exist(1)\n";
var_dump(FAttrezzatura::exist(1));

echo "\nload(1)\n";
print_r(FAttrezzatura::load(1));

echo "\nloadByGhostKitchen(1)\n";
print_r(FAttrezzatura::loadByGhostKitchen(1));
