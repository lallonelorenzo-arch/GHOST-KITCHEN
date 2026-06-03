<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Foundation/FMenu.php';

echo "Test FMenu\n";
echo "==========\n\n";

echo "exist(1)\n";
var_dump(FMenu::exist(1));

echo "\nload(1)\n";
print_r(FMenu::load(1));

echo "\nloadByChef(5)\n";
print_r(FMenu::loadByChef(5));
