<?php
declare(strict_types=1);

require_once __DIR__ . '/../Control/CGestioneDisponibilita.php';

print_r((new CGestioneDisponibilita())->visualizzaCalendario('chef', 1));
print_r((new CGestioneDisponibilita())->aggiungiDisponibilita('chef', 1, '2026-06-20', '12:00', '16:00'));
print_r((new CGestioneDisponibilita())->bloccaDisponibilita('chef', 701));
print_r((new CGestioneDisponibilita())->liberaDisponibilita('chef', 701));
print_r((new CGestioneDisponibilita())->bloccaDisponibilita('ghost_kitchen', 801));

try {
    print_r((new CGestioneDisponibilita())->visualizzaCalendario('altro', 1));
} catch (Throwable $e) {
    echo "Eccezione attesa: {$e->getMessage()}\n";
}

