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
            $chef = FPersistentManager::loadChef((int) $prenotazione->getIdChef());
            $prenotazioni[] = [
                'tipo' => 'chef',
                'prenotazione' => $prenotazione,
                'nomeTarget' => $chef !== null ? trim($chef->getNome() . ' ' . $chef->getCognome()) : 'Chef non disponibile',
                'canReview' => (FPersistentManager::verificaPrenotazioneRecensibile('chef', (int) $prenotazione->getIdPrenotazione(), $idUtente)['recensibile'] ?? false) === true,
            ];
        }

        foreach (FPersistentManager::loadPrenotazioniGhostKitchenByRichiedente($idUtente) as $prenotazione) {
            $ghostKitchen = FPersistentManager::loadGhostKitchen((int) $prenotazione->getIdGhostKitchen());
            $prenotazioni[] = [
                'tipo' => 'ghost_kitchen',
                'prenotazione' => $prenotazione,
                'nomeTarget' => $ghostKitchen !== null ? $ghostKitchen->getNome() : 'Ghost kitchen non disponibile',
                'canReview' => (FPersistentManager::verificaPrenotazioneRecensibile('ghost_kitchen', (int) $prenotazione->getIdPrenotazione(), $idUtente)['recensibile'] ?? false) === true,
            ];
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
