<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Foundation/FPiatto.php';

echo "Test FPiatto\n";
echo "============\n\n";

echo "exist(1)\n";
var_dump(FPiatto::exist(1));

echo "\nload(1)\n";
print_r(FPiatto::load(1));

echo "\nloadByMenu(1)\n";
print_r(FPiatto::loadByMenu(1));
