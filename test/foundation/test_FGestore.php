<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Foundation/FGestore.php';

echo "Test FGestore\n";
echo "=============\n\n";

echo "exist(9)\n";
var_dump(FGestore::exist(9));

echo "\nload(9)\n";
print_r(FGestore::load(9));
