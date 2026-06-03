<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Foundation/FRecensioneGhostKitchen.php';

echo "Test FRecensioneGhostKitchen\n";
echo "============================\n\n";

echo "exist(3)\n";
var_dump(FRecensioneGhostKitchen::exist(3));

echo "\nload(3)\n";
print_r(FRecensioneGhostKitchen::load(3));

echo "\naggiornaValutazioneGhostKitchen(1)\n";
print_r(FRecensioneGhostKitchen::aggiornaValutazioneGhostKitchen(1));
