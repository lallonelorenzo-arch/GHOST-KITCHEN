<?php
declare(strict_types=1);

require_once __DIR__ . '/../Control/CGestioneDisponibilita.php';

print_r(CGestioneDisponibilita::visualizzaCalendario('chef', 1));
print_r(CGestioneDisponibilita::aggiungiDisponibilita('chef', 1, '2026-06-20', '12:00', '16:00'));
print_r(CGestioneDisponibilita::bloccaDisponibilita('chef', 701));
print_r(CGestioneDisponibilita::liberaDisponibilita('chef', 701));
print_r(CGestioneDisponibilita::bloccaDisponibilita('ghost_kitchen', 801));

try {
    print_r(CGestioneDisponibilita::visualizzaCalendario('altro', 1));
} catch (Throwable $e) {
    echo "Eccezione attesa: {$e->getMessage()}\n";
}
