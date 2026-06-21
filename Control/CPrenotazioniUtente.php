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
            $prenotazione = $this->completaSePassata($prenotazione);
            $chef = FPersistentManager::loadChef((int) $prenotazione->getIdChef());
            $prenotazioni[] = [
                'tipo' => 'chef',
                'prenotazione' => $prenotazione,
                'nomeTarget' => $chef !== null ? trim($chef->getNome() . ' ' . $chef->getCognome()) : 'Chef non disponibile',
                'canReview' => (FPersistentManager::verificaPrenotazioneRecensibile('chef', (int) $prenotazione->getIdPrenotazione(), $idUtente)['recensibile'] ?? false) === true,
            ];
        }

        foreach (FPersistentManager::loadPrenotazioniGhostKitchenByRichiedente($idUtente) as $prenotazione) {
            $prenotazione = $this->completaSePassata($prenotazione);
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

    private function completaSePassata(EPrenotazione $prenotazione): EPrenotazione
    {
        if ($prenotazione->getStato() !== EPrenotazione::STATO_PAGATA || $prenotazione->getDataServizio() >= date('Y-m-d')) {
            return $prenotazione;
        }

        $prenotazione->completa();

        if ($prenotazione instanceof EPrenotazioneChef) {
            $aggiornata = FPersistentManager::updatePrenotazioneChef($prenotazione);
            return $aggiornata !== false ? $aggiornata : $prenotazione;
        }

        if ($prenotazione instanceof EPrenotazioneGhostKitchen) {
            $aggiornata = FPersistentManager::updatePrenotazioneGhostKitchen($prenotazione);
            return $aggiornata !== false ? $aggiornata : $prenotazione;
        }

        return $prenotazione;
    }
}
