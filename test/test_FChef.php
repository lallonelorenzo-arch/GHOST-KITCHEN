<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

$idChef = 5;

echo "Exist chef 5: ";
var_dump(FPersistentManager::loadChef($idChef) !== null);

$chef = FPersistentManager::loadChef($idChef);

echo PHP_EOL . "Chef caricato:" . PHP_EOL;
print_r($chef);

echo PHP_EOL . "====================" . PHP_EOL;
echo PHP_EOL;

$risultati = FPersistentManager::cercaChef(
    '',
    'giapponese',
    250,
    4
);

echo "Risultati ricerca chef:" . PHP_EOL;
print_r($risultati);
