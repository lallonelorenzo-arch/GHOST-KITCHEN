<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Foundation/FConnectionDB.php';

try {
    $pdo = FConnectionDB::getInstance()->getConnection();

    echo "Connessione OK\n";

    $stmt = $pdo->query('SELECT COUNT(*) AS totale FROM utenti');
    $row = $stmt->fetch();

    echo "Utenti presenti nel DB: " . $row['totale'] . "\n";
} catch (Throwable $e) {
    echo "Errore connessione DB\n";
    echo $e->getMessage() . "\n";

    if ($e->getPrevious()) {
        echo "Dettaglio PDO: " . $e->getPrevious()->getMessage() . "\n";
    }
}
