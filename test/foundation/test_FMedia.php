<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Foundation/FMedia.php';

echo "Test FMedia\n";
echo "===========\n\n";

echo "exist(1)\n";
var_dump(FMedia::exist(1));

echo "\nload(1)\n";
print_r(FMedia::load(1));

echo "\nloadByOwner('chef', 5)\n";
print_r(FMedia::loadByOwner('chef', 5));

echo "\ngetPrincipale('chef', 5)\n";
print_r(FMedia::getPrincipale('chef', 5));
