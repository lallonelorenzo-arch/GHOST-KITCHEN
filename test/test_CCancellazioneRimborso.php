<?php
declare(strict_types=1);

require_once __DIR__ . '/../Control/CCancellazioneRimborso.php';

echo "UC9 - avvia cancellazione\n";
print_r(CCancellazioneRimborso::avviaCancellazione('chef', 1002, 10));

echo "\nUC9 - calcola rimborso\n";
print_r(CCancellazioneRimborso::calcolaRimborsoStimato('chef', 1002));

echo "\nUC9 - conferma cancellazione\n";
print_r(CCancellazioneRimborso::confermaCancellazione([
    'tipoPrenotazione' => 'chef',
    'idPrenotazione' => 1002,
    'idRichiedente' => 10,
    'motivo' => 'Imprevisto personale'
]));

echo "\nUC9 - prenotazione non trovata\n";
print_r(CCancellazioneRimborso::avviaCancellazione('chef', 9999, 10));
