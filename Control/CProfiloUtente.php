<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

class CProfiloUtente
{
    public function visualizzaProfiloClienteWeb(int $idUtente, array $accesso): array
    {
        if (($accesso['isLogged'] ?? false) !== true || !in_array('chef', $accesso['ruoli'] ?? [], true)) {
            return ['errore' => 'Accesso non consentito'];
        }

        $utente = FPersistentManager::loadUtente($idUtente);
        if ($utente === null) {
            return ['errore' => 'Utente non trovato'];
        }

        return [
            'utenteProfilo' => $utente,
            'accesso' => $accesso,
        ];
    }
}
