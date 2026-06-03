<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Foundation/FRecensione.php';

echo "Test FRecensione\n";
echo "================\n\n";

echo "exist(1)\n";
var_dump(FRecensione::exist(1));

echo "\nloadBase(1)\n";
print_r(FRecensione::loadBase(1));

echo "\nload(1)\n";
print_r(FRecensione::load(1));
