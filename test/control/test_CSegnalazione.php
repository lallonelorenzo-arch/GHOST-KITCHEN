<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Control/CSegnalazione.php';

echo "UC11 - avvia segnalazione\n";
print_r((new CSegnalazione())->avviaSegnalazione(10, 'recensione', 3001));

echo "\nUC11 - invia segnalazione\n";
print_r((new CSegnalazione())->inviaSegnalazione([
    'idSegnalante' => 10,
    'tipoTarget' => 'recensione',
    'idTarget' => 3001,
    'motivo' => 'Contenuto non conforme',
    'descrizione' => 'Richiede verifica da parte dello staff.'
]));

echo "\nUC11 - target non trovato\n";
print_r((new CSegnalazione())->avviaSegnalazione(10, 'recensione', 9999));

echo "\nUC11 - input non valido\n";
try {
    (new CSegnalazione())->avviaSegnalazione(10, 'ordine', 1);
} catch (InvalidArgumentException $e) {
    print_r(['eccezione' => $e->getMessage()]);
}

