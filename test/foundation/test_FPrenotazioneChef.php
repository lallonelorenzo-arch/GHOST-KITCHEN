<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Foundation/FPrenotazioneChef.php';

echo "Test FPrenotazioneChef\n";
echo "======================\n\n";

echo "exist(1)\n";
var_dump(FPrenotazioneChef::exist(1));

echo "\nload(1)\n";
print_r(FPrenotazioneChef::load(1));

echo "\nloadRichieste(5)\n";
print_r(FPrenotazioneChef::loadRichieste(5));

echo "\nverificaRecensibile(1, 1)\n";
print_r(FPrenotazioneChef::verificaRecensibile(1, 1));
