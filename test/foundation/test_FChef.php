<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Foundation/FChef.php';

echo "Test FChef\n";
echo "==========\n\n";

echo "exist(5)\n";
var_dump(FChef::exist(5));

echo "\nload(5)\n";
print_r(FChef::load(5));

echo "\nsearch('Milano', 'mediterranea', 300.0, 4)\n";
print_r(FChef::search('Milano', 'mediterranea', 300.0, 4));
