<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';
require_once __DIR__ . '/../Foundation/FSession.php';

class CAutenticazione
{
    public function mostraLogin(): array
    {
        return [
            'email' => '',
            'errore' => null,
        ];
    }

    public function login(array $dati): array
    {
        $email = strtolower(trim((string) ($dati['email'] ?? '')));
        $password = (string) ($dati['password'] ?? '');

        if ($email === '' || $password === '') {
            return [
                'successo' => false,
                'email' => $email,
                'errore' => 'Inserisci email e password.',
            ];
        }

        if (!FPersistentManager::login($email, $password)) {
            return [
                'successo' => false,
                'email' => $email,
                'errore' => 'Credenziali non valide o utente non attivo.',
            ];
        }

        return ['successo' => true];
    }

    public function logout(): void
    {
        FSession::logout();
    }
}
