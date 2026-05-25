<?php
declare(strict_types=1);

require_once __DIR__ . '/../Control/CDashboardStatistiche.php';

echo "UC14 - dashboard senza filtri\n";
print_r((new CDashboardStatistiche())->visualizzaDashboard());

echo "\nUC14 - dashboard con filtri\n";
print_r((new CDashboardStatistiche())->visualizzaDashboard([
    'dataDa' => '2026-05-01',
    'dataA' => '2026-05-31',
    'tipoPrenotazione' => 'chef'
]));

echo "\nUC14 - input non valido\n";
try {
    (new CDashboardStatistiche())->visualizzaDashboard(['tipoPrenotazione' => 'pagamenti']);
} catch (InvalidArgumentException $e) {
    print_r(['eccezione' => $e->getMessage()]);
}

