<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Foundation/FAmministratore.php';

echo "Test FAmministratore\n";
echo "====================\n\n";

echo "exist(12)\n";
var_dump(FAmministratore::exist(12));

echo "\nload(12)\n";
print_r(FAmministratore::load(12));
