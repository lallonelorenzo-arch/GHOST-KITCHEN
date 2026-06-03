<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Foundation/FRimborso.php';

echo "Test FRimborso\n";
echo "==============\n\n";

echo "exist(1)\n";
var_dump(FRimborso::exist(1));

echo "\nload(1)\n";
print_r(FRimborso::load(1));
