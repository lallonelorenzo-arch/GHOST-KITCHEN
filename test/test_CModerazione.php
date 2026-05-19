<?php
declare(strict_types=1);

require_once __DIR__ . '/../Control/CModerazione.php';

echo "UC13 - contenuti da moderare\n";
print_r(CModerazione::visualizzaContenutiDaModerare());

echo "\nUC13 - prendi in carico segnalazione\n";
print_r(CModerazione::prendiInCaricoSegnalazione(4001));

echo "\nUC13 - modera recensione\n";
print_r(CModerazione::moderaRecensione(3001, 'nascondi'));

echo "\nUC13 - modera profilo\n";
print_r(CModerazione::moderaProfilo(10, 'sospendi'));

echo "\nUC13 - chiudi segnalazione\n";
print_r(CModerazione::chiudiSegnalazione(4002, 'risolta', 'Verifica completata.'));

echo "\nUC13 - input non valido\n";
try {
    CModerazione::moderaRecensione(3001, 'elimina');
} catch (InvalidArgumentException $e) {
    print_r(['eccezione' => $e->getMessage()]);
}
