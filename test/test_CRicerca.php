<?php
declare(strict_types=1);

require_once __DIR__ . '/../Control/CRicerca.php';

print_r(CRicerca::avviaRicerca());

echo PHP_EOL;
echo "====================" . PHP_EOL;
echo PHP_EOL;

$filtri = [
    'localita' => 'Roma',
    'tipologiaCucina' => 'sushi',
    'budgetMax' => 100,
    'valutazioneMin' => 4,
    'tipoRisultato' => 'tutti'
];

print_r(CRicerca::cercaOfferte($filtri));
