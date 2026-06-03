<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Foundation/FPrenotazione.php';

echo "Test FPrenotazione\n";
echo "==================\n\n";

echo "exist(1)\n";
var_dump(FPrenotazione::exist(1));

echo "\nloadBase(1)\n";
print_r(FPrenotazione::loadBase(1));
