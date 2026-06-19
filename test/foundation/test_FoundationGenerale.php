<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Foundation/FPersistentManager.php';

ob_start();
FSession::start();

echo "Foundation smoke test\n";
echo "=====================\n\n";

echo "1. Connessione DB\n";
$pdo = FPersistentManager::getConnection();
echo "Connessione OK\n";
echo "Utenti presenti nel DB: " . $pdo->query('SELECT COUNT(*) FROM utenti')->fetchColumn() . "\n\n";

echo "2. Caricamento utenti e credenziali\n";
print_r(FPersistentManager::loadUtente(1));
print_r(FPersistentManager::loadUtenteByEmail('marco.rinaldi@gk.it'));
print_r(FPersistentManager::verificaCredenziali('marco.rinaldi@gk.it', 'Password123!'));
print_r(FPersistentManager::getRuoliUtente(8));
echo "\n";

echo "3. Login e sessione\n";
var_dump(FPersistentManager::login('irene.villa@gk.it', 'Password123!'));
print_r([
    'logged' => FSession::isLogged(),
    'idUtente' => FSession::getIdUtente(),
    'email' => FSession::getEmail(),
    'ruoli' => FSession::getRuoli(),
    'ruoloAttivo' => FSession::getRuoloAttivo(),
]);
FSession::logout();
echo "\n";

echo "4. Caricamento Entity principali\n";
print_r(FPersistentManager::loadCliente(1));
print_r(FPersistentManager::loadChef(5));
print_r(FPersistentManager::loadGestore(9));
print_r(FPersistentManager::loadAmministratore(12));
print_r(FPersistentManager::loadGhostKitchen(1));
echo "\n";

echo "5. Liste utili ai casi d'uso\n";
print_r(FPersistentManager::loadMenuByChef(5));
print_r(FPersistentManager::loadPiattiByMenu(1));
print_r(FPersistentManager::loadAttrezzatureByGhostKitchen(1));
print_r(FPersistentManager::loadDisponibilitaChef(5));
print_r(FPersistentManager::loadDisponibilitaGhostKitchen(1));
print_r(FPersistentManager::cercaChef('Milano', 'mediterranea', 300.0, 4));
print_r(FPersistentManager::cercaGhostKitchen('Milano', 50.0, 4));
echo "\n";

echo "6. Record operativi principali\n";
print_r(FPersistentManager::loadPrenotazioneChef(1));
print_r(FPersistentManager::loadPrenotazioneGhostKitchen(6));
print_r(FPersistentManager::loadPagamento(1));
print_r(FPersistentManager::loadRecensione(1));
print_r(FPersistentManager::loadRecensioneChef(1));
print_r(FPersistentManager::loadRecensioneGhostKitchen(3));
print_r(FPersistentManager::loadSegnalazione(1));
echo "\n";

echo "7. Store/update/delete con rollback\n";
$pdo->beginTransaction();

try {
    $attrezzatura = new EAttrezzatura(
        null,
        1,
        'Test Foundation ' . time(),
        'test',
        'Record temporaneo Foundation',
        1
    );

    $salvata = FPersistentManager::storeAttrezzatura($attrezzatura);
    print_r($salvata);

    if ($salvata instanceof EAttrezzatura) {
        $salvata->setQuantita(3);
        $aggiornata = FPersistentManager::updateAttrezzatura($salvata);
        print_r($aggiornata);

        $ricaricata = FPersistentManager::loadAttrezzatura((int) $salvata->getIdAttrezzatura());
        print_r($ricaricata);

        $eliminata = FAttrezzatura::delete((int) $salvata->getIdAttrezzatura());
        var_dump($eliminata);
    }

    $pdo->rollBack();
    echo "Rollback eseguito: nessun dato di test lasciato nel DB.\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo "Errore nel test store/update/delete\n";
    echo $e->getMessage() . "\n";
}
