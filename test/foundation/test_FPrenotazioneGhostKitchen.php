<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Foundation/FPrenotazioneGhostKitchen.php';

echo "Test FPrenotazioneGhostKitchen\n";
echo "==============================\n\n";

echo "exist(6)\n";
var_dump(FPrenotazioneGhostKitchen::exist(6));

echo "\nload(6)\n";
print_r(FPrenotazioneGhostKitchen::load(6));

echo "\nloadRichiesteByGestore(9)\n";
print_r(FPrenotazioneGhostKitchen::loadRichiesteByGestore(9));

echo "\nverificaRecensibile(6, 5)\n";
print_r(FPrenotazioneGhostKitchen::verificaRecensibile(6, 5));
