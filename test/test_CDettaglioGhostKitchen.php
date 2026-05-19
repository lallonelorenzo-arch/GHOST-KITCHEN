<?php
declare(strict_types=1);

require_once __DIR__ . '/../Control/CDettaglioGhostKitchen.php';

print_r(CDettaglioGhostKitchen::visualizzaDettaglioGhostKitchen(101));

try {
    print_r(CDettaglioGhostKitchen::visualizzaDettaglioGhostKitchen(0));
} catch (Throwable $e) {
    echo "Eccezione attesa: {$e->getMessage()}\n";
}

print_r(CDettaglioGhostKitchen::visualizzaDettaglioGhostKitchen(9999));
