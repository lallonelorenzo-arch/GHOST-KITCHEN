<?php
declare(strict_types=1);

require_once __DIR__ . '/../Control/CRecensione.php';

echo "UC10 - avvia recensione chef\n";
print_r(CRecensione::avviaRecensione('chef', 1001, 10));

echo "\nUC10 - pubblica recensione chef\n";
print_r(CRecensione::pubblicaRecensione([
    'tipoTarget' => 'chef',
    'idPrenotazione' => 1001,
    'idAutore' => 10,
    'punteggio' => 5,
    'commento' => 'Esperienza ottima e servizio puntuale.'
]));

echo "\nUC10 - prenotazione non recensibile\n";
print_r(CRecensione::avviaRecensione('chef', 1002, 10));

echo "\nUC10 - input non valido\n";
try {
    CRecensione::pubblicaRecensione([
        'tipoTarget' => 'chef',
        'idPrenotazione' => 1001,
        'idAutore' => 10,
        'punteggio' => 6,
        'commento' => 'Punteggio non valido'
    ]);
} catch (InvalidArgumentException $e) {
    print_r(['eccezione' => $e->getMessage()]);
}
