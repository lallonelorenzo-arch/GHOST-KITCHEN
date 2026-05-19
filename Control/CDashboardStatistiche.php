<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

class CDashboardStatistiche
{
    public static function visualizzaDashboard(array $filtri = []): array
    {
        $filtriNormalizzati = [
            'dataDa' => trim((string) ($filtri['dataDa'] ?? '')),
            'dataA' => trim((string) ($filtri['dataA'] ?? '')),
            'tipoPrenotazione' => strtolower(trim((string) ($filtri['tipoPrenotazione'] ?? 'tutte')))
        ];

        if (!in_array($filtriNormalizzati['tipoPrenotazione'], ['chef', 'ghost_kitchen', 'tutte'], true)) {
            throw new InvalidArgumentException('Tipo prenotazione filtro non valido.');
        }

        return [
            'filtri' => $filtriNormalizzati,
            'statistiche' => FPersistentManager::getStatisticheDashboard($filtriNormalizzati),
            'azioni' => [
                'visualizzaDashboard' => '/DashboardStatistiche/visualizzaDashboard'
            ]
        ];
    }
}
