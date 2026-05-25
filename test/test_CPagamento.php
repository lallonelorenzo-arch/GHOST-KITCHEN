<?php
declare(strict_types=1);

require_once __DIR__ . '/../Control/CPagamento.php';

print_r((new CPagamento())->avviaPagamento('chef', 901, 'caparra'));
print_r((new CPagamento())->selezionaMetodoPagamento(1001));
print_r((new CPagamento())->confermaPagamento([
    'tipoPrenotazione' => 'chef',
    'idPrenotazione' => 901,
    'tipoPagamento' => 'caparra',
    'idMetodoPagamento' => 1001
]));

print_r((new CPagamento())->selezionaMetodoPagamento(9999));

try {
    print_r((new CPagamento())->avviaPagamento('chef', 0, 'totale'));
} catch (Throwable $e) {
    echo "Eccezione attesa: {$e->getMessage()}\n";
}

