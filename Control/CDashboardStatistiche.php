<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

class CDashboardStatistiche
{
    public function visualizzaDashboard(array $filtri = []): array
    {
        $filtriNormalizzati = $this->normalizzaFiltri($filtri);

        return [
            'filtri' => $filtriNormalizzati,
            'statistiche' => FPersistentManager::getStatisticheDashboard($filtriNormalizzati),
            'azioni' => [
                'visualizzaDashboard' => '/DashboardStatistiche/visualizzaDashboard'
            ]
        ];
    }

    public function visualizzaDashboardWeb(array $accesso, array $query = []): array
    {
        $ruoli = $accesso['ruoli'] ?? [];
        if (($accesso['isLogged'] ?? false) !== true || (!in_array('admin', $ruoli, true) && !in_array('amministratore', $ruoli, true))) {
            return [
                'accessoRichiesto' => true,
                'messaggioAccesso' => 'Non hai permessi per questa sezione.',
                'filtri' => [
                    'dataDa' => '',
                    'dataA' => '',
                    'tipoPrenotazione' => 'tutte',
                ],
                'statistiche' => [],
            ];
        }

        try {
            return $this->visualizzaDashboard($query) + ['accesso' => $accesso];
        } catch (InvalidArgumentException $exception) {
            $fallback = $this->visualizzaDashboard([]);
            $fallback['messaggioFiltro'] = $exception->getMessage();
            $fallback['accesso'] = $accesso;
            return $fallback;
        }
    }

    private function normalizzaFiltri(array $filtri): array
    {
        $periodo = strtolower(trim((string) ($filtri['periodo'] ?? 'personalizzato')));
        if (!in_array($periodo, ['personalizzato', 'mese', 'trimestre', 'anno'], true)) {
            $periodo = 'personalizzato';
        }

        $tipoPrenotazione = strtolower(trim((string) ($filtri['tipoPrenotazione'] ?? 'tutte')));
        if (!in_array($tipoPrenotazione, ['chef', 'ghost_kitchen', 'tutte'], true)) {
            throw new InvalidArgumentException('Tipo prenotazione filtro non valido.');
        }

        $dataDa = trim((string) ($filtri['dataDa'] ?? ''));
        $dataA = trim((string) ($filtri['dataA'] ?? ''));

        if ($periodo !== 'personalizzato') {
            [$dataDa, $dataA] = $this->intervalloPeriodo($periodo);
        }

        if ($dataDa !== '' && !$this->isDate($dataDa)) {
            throw new InvalidArgumentException('La data iniziale del filtro non e valida.');
        }

        if ($dataA !== '' && !$this->isDate($dataA)) {
            throw new InvalidArgumentException('La data finale del filtro non e valida.');
        }

        if ($dataDa !== '' && $dataA !== '' && $dataDa > $dataA) {
            throw new InvalidArgumentException('La data iniziale non puo essere successiva alla data finale.');
        }

        return [
            'dataDa' => $dataDa,
            'dataA' => $dataA,
            'tipoPrenotazione' => $tipoPrenotazione,
            'periodo' => $periodo,
        ];
    }

    private function intervalloPeriodo(string $periodo): array
    {
        $oggi = new DateTimeImmutable('today');

        if ($periodo === 'mese') {
            return [$oggi->modify('first day of this month')->format('Y-m-d'), $oggi->format('Y-m-d')];
        }

        if ($periodo === 'trimestre') {
            $mese = (int) $oggi->format('n');
            $inizioTrimestre = intdiv($mese - 1, 3) * 3 + 1;
            return [$oggi->setDate((int) $oggi->format('Y'), $inizioTrimestre, 1)->format('Y-m-d'), $oggi->format('Y-m-d')];
        }

        return [$oggi->setDate((int) $oggi->format('Y'), 1, 1)->format('Y-m-d'), $oggi->format('Y-m-d')];
    }

    private function isDate(string $date): bool
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }
}

