<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Foundation/FGhostKitchen.php';

echo "Test FGhostKitchen\n";
echo "==================\n\n";

echo "exist(1)\n";
var_dump(FGhostKitchen::exist(1));

echo "\nload(1)\n";
print_r(FGhostKitchen::load(1));

echo "\nsearch('Milano', 50.0, 4)\n";
print_r(FGhostKitchen::search('Milano', 50.0, 4));
