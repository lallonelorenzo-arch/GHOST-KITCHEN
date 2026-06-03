<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Foundation/FUtente.php';

echo "Test FUtente\n";
echo "============\n\n";

echo "exist(1)\n";
var_dump(FUtente::exist(1));

echo "\nload(1)\n";
print_r(FUtente::load(1));

echo "\nloadByEmail('marco.rinaldi@gk.it')\n";
print_r(FUtente::loadByEmail('marco.rinaldi@gk.it'));

echo "\nverificaCredenziali('marco.rinaldi@gk.it', 'Password123!')\n";
print_r(FUtente::verificaCredenziali('marco.rinaldi@gk.it', 'Password123!'));

echo "\ngetRuoli(8)\n";
print_r(FUtente::getRuoli(8));

echo "\nemailExists('marco.rinaldi@gk.it')\n";
var_dump(FUtente::emailExists('marco.rinaldi@gk.it'));
