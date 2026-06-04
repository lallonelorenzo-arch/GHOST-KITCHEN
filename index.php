<?php
declare(strict_types=1);

require_once __DIR__ . '/Control/CFrontController.php';

$frontController = new CFrontController(); //ogni pagina visitata passa da index -> chiama il CFrontController -> CPage -> VPage
$frontController->handle();
