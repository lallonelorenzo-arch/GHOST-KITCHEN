<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

/**
 * Control per UC1 - Navigazione e Ricerca.
 */

class CRicerca
{
    
    public function avviaRicerca(): array
    {
        return [
            'campi' => [
                'localita' => '',
                'tipologiaCucina' => '',
                'budgetMax' => 0.0,
                'valutazioneMin' => 0,
                'tipoRisultato' => 'tutti'
            ],
            'opzioniTipoRisultato' => ['chef', 'ghost_kitchen', 'tutti'],
            'url' => [
                'avviaRicerca' => '/Ricerca/avviaRicerca',
                'cercaOfferte' => '/Ricerca/cercaOfferte'
            ]
        ];
    }

    /**
     * Normalizza i filtri e richiede i risultati al PersistentManager fittizio.
     */
    public function cercaOfferte(array $filtri): array
    {
        $filtriNormalizzati = $this->normalizzaFiltri($filtri);

        $risultatiChef = [];
        $risultatiGhostKitchen = [];

        if (
            $filtriNormalizzati['tipoRisultato'] === 'chef' ||
            $filtriNormalizzati['tipoRisultato'] === 'tutti'
        ) {
            $risultatiChef = FPersistentManager::cercaChef(
                $filtriNormalizzati['localita'],
                $filtriNormalizzati['tipologiaCucina'],
                $filtriNormalizzati['budgetMax'],
                $filtriNormalizzati['valutazioneMin']
            );
        }

        if (
            $filtriNormalizzati['tipoRisultato'] === 'ghost_kitchen' ||
            $filtriNormalizzati['tipoRisultato'] === 'tutti'
        ) {
            $risultatiGhostKitchen = FPersistentManager::cercaGhostKitchen(
                $filtriNormalizzati['localita'],
                $filtriNormalizzati['budgetMax'],
                $filtriNormalizzati['valutazioneMin']
            );
        }

        return [
            'filtri' => $filtriNormalizzati,
            'chef' => $risultatiChef,
            'ghostKitchen' => $risultatiGhostKitchen
        ];
    }

    private function normalizzaFiltri(array $filtri): array
    {
        $localita = trim((string) ($filtri['localita'] ?? ''));
        $tipologiaCucina = trim((string) ($filtri['tipologiaCucina'] ?? ''));

        $budgetMax = (float) ($filtri['budgetMax'] ?? 0);
        if ($budgetMax < 0) {
            $budgetMax = 0.0;
        }

        $valutazioneMin = (int) ($filtri['valutazioneMin'] ?? 0);
        if ($valutazioneMin < 0) {
            $valutazioneMin = 0;
        }
        if ($valutazioneMin > 5) {
            $valutazioneMin = 5;
        }

        $tipoRisultato = strtolower(trim((string) ($filtri['tipoRisultato'] ?? 'tutti')));
        $tipiAmmessi = ['chef', 'ghost_kitchen', 'tutti'];
        if (!in_array($tipoRisultato, $tipiAmmessi, true)) {
            $tipoRisultato = 'tutti';
        }

        return [
            'localita' => $localita,
            'tipologiaCucina' => $tipologiaCucina,
            'budgetMax' => $budgetMax,
            'valutazioneMin' => $valutazioneMin,
            'tipoRisultato' => $tipoRisultato
        ];
    }
}

