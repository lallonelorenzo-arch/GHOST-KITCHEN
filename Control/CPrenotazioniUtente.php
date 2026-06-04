<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

class CPrenotazioniUtente
{
    public function visualizzaPrenotazioniWeb(array $accesso): array
    {
        if (($accesso['isLogged'] ?? false) !== true) {
            return [
                'accessoRichiesto' => true,
                'messaggioAccesso' => 'Accedi per vedere le tue prenotazioni.',
                'prenotazioni' => [],
            ];
        }

        $idUtente = (int) ($accesso['idUtente'] ?? 0);
        $prenotazioni = [];

        foreach (FPersistentManager::loadPrenotazioniChefByRichiedente($idUtente) as $prenotazione) {
            $prenotazioni[] = ['tipo' => 'chef', 'prenotazione' => $prenotazione];
        }

        foreach (FPersistentManager::loadPrenotazioniGhostKitchenByRichiedente($idUtente) as $prenotazione) {
            $prenotazioni[] = ['tipo' => 'ghost_kitchen', 'prenotazione' => $prenotazione];
        }

        usort($prenotazioni, static function (array $a, array $b): int {
            $pa = $a['prenotazione'];
            $pb = $b['prenotazione'];
            return strcmp($pb->getDataServizio() . $pb->getOraInizio(), $pa->getDataServizio() . $pa->getOraInizio());
        });

        return [
            'accesso' => $accesso,
            'prenotazioni' => $prenotazioni,
        ];
    }
}
