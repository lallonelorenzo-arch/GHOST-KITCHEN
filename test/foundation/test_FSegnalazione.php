<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Foundation/FSegnalazione.php';

echo "Test FSegnalazione\n";
echo "==================\n\n";

echo "exist(1)\n";
var_dump(FSegnalazione::exist(1));

echo "\nload(1)\n";
print_r(FSegnalazione::load(1));

echo "\nloadByStato('aperta')\n";
print_r(FSegnalazione::loadByStato(ESegnalazione::STATO_APERTA));

echo "\nloadTarget('recensione', 4)\n";
print_r(FSegnalazione::loadTarget(ESegnalazione::TARGET_RECENSIONE, 4));
