<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Foundation/FRecensioneChef.php';

echo "Test FRecensioneChef\n";
echo "====================\n\n";

echo "exist(1)\n";
var_dump(FRecensioneChef::exist(1));

echo "\nload(1)\n";
print_r(FRecensioneChef::load(1));

echo "\naggiornaValutazioneChef(5)\n";
print_r(FRecensioneChef::aggiornaValutazioneChef(5));
