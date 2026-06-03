<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Control/CPrenotazioneGhostKitchen.php';

print_r((new CPrenotazioneGhostKitchen())->avviaPrenotazioneGhostKitchen(10, 'cliente', 101));
print_r((new CPrenotazioneGhostKitchen())->selezionaDisponibilitaGhostKitchen(801));
print_r((new CPrenotazioneGhostKitchen())->inserisciDatiPrenotazioneGhostKitchen([
    'idGhostKitchen' => 101,
    'dataServizio' => '2026-06-12',
    'oraInizio' => '10:00',
    'oraFine' => '14:00'
]));
print_r((new CPrenotazioneGhostKitchen())->confermaPrenotazioneGhostKitchen([
    'idRichiedente' => 10,
    'tipoRichiedente' => 'cliente',
    'idGhostKitchen' => 101,
    'dataServizio' => '2026-06-12',
    'oraInizio' => '10:00',
    'oraFine' => '14:00',
    'note' => 'Test UC5'
]));

print_r((new CPrenotazioneGhostKitchen())->selezionaDisponibilitaGhostKitchen(9999));

