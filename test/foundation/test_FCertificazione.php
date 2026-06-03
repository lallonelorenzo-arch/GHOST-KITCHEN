<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Foundation/FCertificazione.php';

echo "Test FCertificazione\n";
echo "====================\n\n";

echo "exist(1)\n";
var_dump(FCertificazione::exist(1));

echo "\nload(1)\n";
print_r(FCertificazione::load(1));

echo "\nloadByChef(5)\n";
print_r(FCertificazione::loadByChef(5));

echo "\nloadByStato('in_attesa')\n";
print_r(FCertificazione::loadByStato('in_attesa'));
