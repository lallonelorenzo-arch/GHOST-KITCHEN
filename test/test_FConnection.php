<?php
declare(strict_types=1);

// Carica la facciata della Foundation, che a sua volta include FConnectionDB.
require_once __DIR__ . '/../Foundation/FPersistentManager.php';

try {
    // Prova ad aprire una connessione PDO verso il database configurato in FConnectionDB.
    $pdo = FPersistentManager::getConnection();

    // Se non viene lanciata nessuna eccezione, la connessione al database funziona.
    echo "Connessione OK" . PHP_EOL;
} catch (Throwable $e) {
    // Se credenziali, nome database o MySQL non sono corretti, l'errore finisce qui.
    echo "ERRORE: " . $e->getMessage() . PHP_EOL;
}
