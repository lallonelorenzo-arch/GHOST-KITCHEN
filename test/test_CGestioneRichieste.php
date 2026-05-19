<?php
declare(strict_types=1);

require_once __DIR__ . '/../Control/CGestioneRichieste.php';

print_r(CGestioneRichieste::visualizzaRichieste('chef', 1));
print_r(CGestioneRichieste::accettaRichiesta('chef', 901));
print_r(CGestioneRichieste::rifiutaRichiesta('ghost_kitchen', 902, 'Slot non compatibile'));
print_r(CGestioneRichieste::visualizzaRichieste('gestore', 21));

try {
    print_r(CGestioneRichieste::accettaRichiesta('x', 901));
} catch (Throwable $e) {
    echo "Eccezione attesa: {$e->getMessage()}\n";
}
