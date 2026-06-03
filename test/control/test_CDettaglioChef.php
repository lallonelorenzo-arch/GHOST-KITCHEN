<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Control/CDettaglioChef.php';

print_r((new CDettaglioChef())->visualizzaDettaglioChef(1));

echo PHP_EOL;
echo "====================" . PHP_EOL;
echo PHP_EOL;

print_r((new CDettaglioChef())->visualizzaDettaglioChef(999));

