<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Foundation/FCancellazione.php';

echo "Test FCancellazione\n";
echo "===================\n\n";

echo "exist(1)\n";
var_dump(FCancellazione::exist(1));

echo "\nload(1)\n";
print_r(FCancellazione::load(1));

echo "\ncalcolaRimborsoStimato('chef', 2)\n";
print_r(FCancellazione::calcolaRimborsoStimato(ECancellazione::PRENOTAZIONE_CHEF, 2));
