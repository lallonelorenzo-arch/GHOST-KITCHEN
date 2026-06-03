<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Foundation/FCliente.php';

echo "Test FCliente\n";
echo "=============\n\n";

echo "exist(1)\n";
var_dump(FCliente::exist(1));

echo "\nload(1)\n";
print_r(FCliente::load(1));
