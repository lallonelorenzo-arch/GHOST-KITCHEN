<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Foundation/FSession.php';

ob_start();
FSession::start();

echo "Test FSession\n";
echo "=============\n\n";

echo "set/get/has/remove\n";
FSession::set('test_key', 'test_value');
var_dump(FSession::has('test_key'));
var_dump(FSession::get('test_key'));
FSession::remove('test_key');
var_dump(FSession::has('test_key'));

echo "\nlogin/logout e ruoli\n";
FSession::login(
    [
        'idUtente' => 99,
        'email' => 'test@example.com',
        'nome' => 'Test',
        'cognome' => 'Sessione',
    ],
    ['cliente', 'chef'],
    'cliente'
);

print_r([
    'logged' => FSession::isLogged(),
    'idUtente' => FSession::getIdUtente(),
    'email' => FSession::getEmail(),
    'nome' => FSession::getNome(),
    'cognome' => FSession::getCognome(),
    'ruoli' => FSession::getRuoli(),
    'ruoloAttivo' => FSession::getRuoloAttivo(),
    'hasChef' => FSession::hasRuolo('chef'),
    'setRuoloAttivoChef' => FSession::setRuoloAttivo('chef'),
]);

FSession::logout();
var_dump(FSession::isLogged());
