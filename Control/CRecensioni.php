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
                'vistaAdmin' => false,
            ];
        }

        $filtri = $this->normalizzaFiltri($query);

        return [
            'titoloPagina' => 'Le mie recensioni',
            'descrizionePagina' => 'Rivedi le valutazioni che hai pubblicato dopo servizi completati.',
            'recensioni' => FPersistentManager::loadRecensioniByAutore((int) $accesso['idUtente'], $filtri),
            'filtri' => $filtri,
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
                'vistaAdmin' => true,
            ];
        }

        $filtri = $this->normalizzaFiltri($query);

        return [
            'titoloPagina' => 'Tutte le recensioni',
            'descrizionePagina' => 'Consulta e filtra le recensioni pubblicate su chef e ghost kitchen.',
            'recensioni' => FPersistentManager::loadCatalogoRecensioni($filtri),
            'filtri' => $filtri,
            'vistaAdmin' => true,
        ];
    }

    private function normalizzaFiltri(array $query): array
    {
        $ordinamento = strtolower(trim((string) ($query['ordinamento'] ?? 'recenti')));
        if (!in_array($ordinamento, ['recenti', 'valutazioni_alte', 'valutazioni_basse'], true)) {
            $ordinamento = 'recenti';
        }

        $tipo = strtolower(trim((string) ($query['tipo'] ?? 'tutte')));
        $tipo = str_replace('-', '_', $tipo);
        if (!in_array($tipo, ['tutte', 'chef', 'ghost_kitchen'], true)) {
            $tipo = 'tutte';
        }

        return [
            'ordinamento' => $ordinamento,
            'tipo' => $tipo,
            'stato' => 'tutti',
            'tipologiaCucina' => '',
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
