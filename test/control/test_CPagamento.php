<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Control/CPagamento.php';

print_r((new CPagamento())->avviaPagamento('chef', 901));
print_r((new CPagamento())->confermaPagamento([
    'tipoPrenotazione' => 'chef',
    'idPrenotazione' => 901,
    'idUtente' => 1
]));

try {
    print_r((new CPagamento())->avviaPagamento('chef', 0));
} catch (Throwable $e) {
    echo "Eccezione attesa: {$e->getMessage()}\n";
}

