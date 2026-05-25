<?php
declare(strict_types=1);

require_once __DIR__ . '/../Control/CValidazioneCertificazioni.php';

echo "UC12 - certificazioni in attesa\n";
print_r((new CValidazioneCertificazioni())->visualizzaCertificazioniInAttesa());

echo "\nUC12 - dettaglio certificazione\n";
print_r((new CValidazioneCertificazioni())->visualizzaDettaglioCertificazione(6001));

echo "\nUC12 - approva certificazione\n";
print_r((new CValidazioneCertificazioni())->approvaCertificazione(6001, 'Documento leggibile e valido.'));

echo "\nUC12 - rifiuta certificazione\n";
print_r((new CValidazioneCertificazioni())->rifiutaCertificazione(6002, 'Documento incompleto.'));

echo "\nUC12 - certificazione non trovata\n";
print_r((new CValidazioneCertificazioni())->visualizzaDettaglioCertificazione(9999));

