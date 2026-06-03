<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Foundation/FStatisticheDashboard.php';

echo "Test FStatisticheDashboard\n";
echo "==========================\n\n";

print_r(FStatisticheDashboard::getStatisticheDashboard([]));
