<?php
declare(strict_types=1);

require_once __DIR__ . '/../Control/CPrenotazioneChef.php';

print_r((new CPrenotazioneChef())->avviaPrenotazioneChef(10, 1));
print_r((new CPrenotazioneChef())->selezionaMenu(301));
print_r((new CPrenotazioneChef())->inserisciDatiPrenotazioneChef([
    'idChef' => 1,
    'dataServizio' => '2026-06-10',
    'oraInizio' => '18:00',
    'oraFine' => '22:00'
]));
print_r((new CPrenotazioneChef())->confermaPrenotazioneChef([
    'idCliente' => 10,
    'idChef' => 1,
    'idMenu' => 301,
    'dataServizio' => '2026-06-10',
    'oraInizio' => '18:00',
    'oraFine' => '22:00',
    'indirizzoServizio' => 'Via Appia 12, Roma',
    'numeroPersone' => 4,
    'richiesteSpeciali' => 'No lattosio',
    'note' => 'Test UC4'
]));

try {
    print_r((new CPrenotazioneChef())->avviaPrenotazioneChef(-1, 1));
} catch (Throwable $e) {
    echo "Eccezione attesa: {$e->getMessage()}\n";
}

