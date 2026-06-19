<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Foundation/FPagamento.php';

echo "Test FPagamento\n";
echo "===============\n\n";

echo "exist(1)\n";
var_dump(FPagamento::exist(1));

echo "\nload(1)\n";
print_r(FPagamento::load(1));

echo "\nloadByPrenotazione('chef', 1)\n";
print_r(FPagamento::loadByPrenotazione(EPagamento::PRENOTAZIONE_CHEF, 1));

echo "\ncalcolaImporto('chef', 1)\n";
var_dump(FPagamento::calcolaImporto(EPagamento::PRENOTAZIONE_CHEF, 1));
