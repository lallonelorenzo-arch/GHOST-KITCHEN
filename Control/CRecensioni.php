<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

class CRecensioni
{
    public function visualizzaMieRecensioniWeb(array $accesso, array $query = []): array
    {
        if (!$this->isLogged($accesso)) {
            return [
                'messaggioAccesso' => 'Accedi per consultare le tue recensioni.',
                'recensioni' => [],
                'filtri' => $this->normalizzaFiltri($query),
                'opzioniTipologiaCucina' => [],
                'vistaAdmin' => false,
            ];
        }

        $filtri = $this->normalizzaFiltri($query);

        return [
            'titoloPagina' => 'Le mie recensioni',
            'descrizionePagina' => 'Rivedi le valutazioni che hai pubblicato dopo servizi completati.',
            'recensioni' => FPersistentManager::loadRecensioniByAutore((int) $accesso['idUtente'], $filtri),
            'filtri' => $filtri,
            'opzioniTipologiaCucina' => FPersistentManager::loadTipologieCucinaRecensite(),
            'vistaAdmin' => false,
        ];
    }

    public function visualizzaTutteRecensioniWeb(array $accesso, array $query = []): array
    {
        if (!$this->isAdmin($accesso)) {
            return [
                'messaggioAccesso' => 'Non hai permessi per consultare tutte le recensioni.',
                'recensioni' => [],
                'filtri' => $this->normalizzaFiltri($query),
                'opzioniTipologiaCucina' => [],
                'vistaAdmin' => true,
            ];
        }

        $filtri = $this->normalizzaFiltri($query);

        return [
            'titoloPagina' => 'Tutte le recensioni',
            'descrizionePagina' => 'Consulta e filtra le recensioni pubblicate su chef e ghost kitchen.',
            'recensioni' => FPersistentManager::loadCatalogoRecensioni($filtri),
            'filtri' => $filtri,
            'opzioniTipologiaCucina' => FPersistentManager::loadTipologieCucinaRecensite(),
            'vistaAdmin' => true,
        ];
    }

    private function normalizzaFiltri(array $query): array
    {
        $ordinamento = strtolower(trim((string) ($query['ordinamento'] ?? 'recenti')));
        if (!in_array($ordinamento, ['recenti', 'valutazioni_alte', 'valutazioni_basse', 'cucina'], true)) {
            $ordinamento = 'recenti';
        }

        $tipo = strtolower(trim((string) ($query['tipo'] ?? 'tutte')));
        $tipo = str_replace('-', '_', $tipo);
        if (!in_array($tipo, ['tutte', 'chef', 'ghost_kitchen'], true)) {
            $tipo = 'tutte';
        }

        $stato = strtolower(trim((string) ($query['stato'] ?? 'tutti')));
        if (!in_array($stato, ['tutti', ERecensione::STATO_VISIBILE, ERecensione::STATO_NASCOSTA, ERecensione::STATO_RIMOSSA], true)) {
            $stato = 'tutti';
        }

        return [
            'ordinamento' => $ordinamento,
            'tipo' => $tipo,
            'stato' => $stato,
            'tipologiaCucina' => trim((string) ($query['tipologiaCucina'] ?? '')),
        ];
    }

    private function isLogged(array $accesso): bool
    {
        return ($accesso['isLogged'] ?? false) === true && (int) ($accesso['idUtente'] ?? 0) > 0;
    }

    private function isAdmin(array $accesso): bool
    {
        $ruoli = $accesso['ruoli'] ?? [];
        return $this->isLogged($accesso) && (in_array('admin', $ruoli, true) || in_array('amministratore', $ruoli, true));
    }
}
