<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Foundation/FMetodoPagamento.php';

echo "Test FMetodoPagamento\n";
echo "=====================\n\n";

echo "exist(1)\n";
var_dump(FMetodoPagamento::exist(1));

echo "\nload(1)\n";
print_r(FMetodoPagamento::load(1));

echo "\nloadByUtente(1)\n";
print_r(FMetodoPagamento::loadByUtente(1));
