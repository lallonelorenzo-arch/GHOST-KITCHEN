<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

class CAdminUtenti
{
    public function visualizzaUtentiWeb(array $accesso, array $query = []): array
    {
        if (!$this->isAdmin($accesso)) {
            return [
                'accessoRichiesto' => true,
                'messaggioAccesso' => 'Non hai permessi per questa sezione.',
                'clienti' => [],
                'chef' => [],
                'gestori' => [],
                'ghostKitchen' => [],
                'riepilogoUtenti' => [],
            ];
        }

        $filtri = $this->filtriUtenti($query);
        $clienti = $this->filtraProfili(FPersistentManager::loadClientiRegistrati(), $filtri, 'cliente');
        $chef = $this->filtraProfili(FPersistentManager::loadChefRegistrati(), $filtri, 'chef');
        $gestori = $this->filtraProfili(FPersistentManager::loadGestoriRegistrati(), $filtri, 'gestore');
        $ghostKitchen = $this->filtraProfili(FPersistentManager::loadGhostKitchenRegistrate(), $filtri, 'ghost_kitchen');

        return [
            'accesso' => $accesso,
            'filtriUtenti' => $filtri,
            'clienti' => $clienti,
            'chef' => $chef,
            'gestori' => $gestori,
            'ghostKitchen' => $ghostKitchen,
            'gestoriGhostKitchen' => $this->mappaGestoriGhostKitchen($ghostKitchen),
            'riepilogoUtenti' => [
                'clienti' => count($clienti),
                'chef' => count($chef),
                'gestori' => count($gestori),
                'ghostKitchen' => count($ghostKitchen),
            ],
        ];
    }

    public function aggiornaStatoUtenteWeb(int $idUtente, string $azione, array $accesso): array
    {
        if (!$this->isAdmin($accesso)) {
            return $this->esito('Accesso non consentito', 'Non hai permessi per questa sezione.', false);
        }

        try {
            $this->validaId($idUtente, 'ID utente non valido.');
            $azione = strtolower(trim($azione));
            $stati = [
                'sospendi' => EUtente::STATO_SOSPESO,
                'banna' => EUtente::STATO_BANNATO,
                'riattiva' => EUtente::STATO_ATTIVO,
            ];

            if (!isset($stati[$azione])) {
                throw new InvalidArgumentException('Azione utente non valida.');
            }

            if (($accesso['idUtente'] ?? null) !== null && (int) $accesso['idUtente'] === $idUtente && $azione !== 'riattiva') {
                return $this->esito('Operazione non consentita', 'Non puoi sospendere o bannare il tuo profilo amministratore mentre sei loggato.', false);
            }

            $utente = FPersistentManager::loadUtente($idUtente);
            if ($utente === null) {
                return $this->esito('Utente non trovato', 'Il profilo richiesto non esiste.', false);
            }

            $utente->setStato($stati[$azione]);
            $utente = FPersistentManager::updateUtente($utente);
            if ($utente === false) {
                return $this->esito('Operazione non completata', 'Profilo non aggiornato.', false);
            }

            return $this->esito('Profilo aggiornato', 'Lo stato dell utente e stato aggiornato correttamente.', true);
        } catch (InvalidArgumentException $exception) {
            return $this->esito('Operazione non completata', $exception->getMessage(), false);
        } catch (Throwable $exception) {
            error_log('[CAdminUtenti] ' . $exception->getMessage());
            return $this->esito('Operazione non completata', 'Errore interno durante l aggiornamento utente.', false);
        }
    }

    public function aggiornaVerificaGestoreWeb(int $idGestore, string $azione, array $accesso): array
    {
        if (!$this->isAdmin($accesso)) {
            return $this->esito('Accesso non consentito', 'Non hai permessi per questa sezione.', false);
        }

        try {
            $this->validaId($idGestore, 'ID gestore non valido.');
            $azione = strtolower(trim($azione));
            $stati = [
                'approva' => EGestore::STATO_VERIFICA_VERIFICATO,
                'rifiuta' => EGestore::STATO_VERIFICA_RIFIUTATO,
                'sospendi-verifica' => EGestore::STATO_VERIFICA_SOSPESO,
                'rimetti-in-attesa' => EGestore::STATO_VERIFICA_IN_ATTESA,
            ];

            if (!isset($stati[$azione])) {
                throw new InvalidArgumentException('Azione verifica gestore non valida.');
            }

            $gestore = FPersistentManager::loadGestore($idGestore);
            if ($gestore === null) {
                return $this->esito('Gestore non trovato', 'Il profilo gestore richiesto non esiste.', false);
            }

            $gestore->setStatoVerifica($stati[$azione]);
            $gestore = FPersistentManager::updateGestore($gestore);
            if ($gestore === false) {
                return $this->esito('Operazione non completata', 'Gestore non aggiornato.', false);
            }

            return $this->esito('Gestore aggiornato', 'Lo stato di verifica del gestore e stato aggiornato correttamente.', true);
        } catch (InvalidArgumentException $exception) {
            return $this->esito('Operazione non completata', $exception->getMessage(), false);
        } catch (Throwable $exception) {
            error_log('[CAdminUtenti] ' . $exception->getMessage());
            return $this->esito('Operazione non completata', 'Errore interno durante l aggiornamento gestore.', false);
        }
    }

    public function aggiornaStatoGhostKitchenWeb(int $idGhostKitchen, string $azione, array $accesso): array
    {
        if (!$this->isAdmin($accesso)) {
            return $this->esito('Accesso non consentito', 'Non hai permessi per questa sezione.', false);
        }

        try {
            $this->validaId($idGhostKitchen, 'ID ghost kitchen non valido.');
            $azione = strtolower(trim($azione));
            $stati = [
                'attiva' => EGhostKitchen::STATO_ATTIVA,
                'sospendi' => EGhostKitchen::STATO_SOSPESA,
                'non-disponibile' => EGhostKitchen::STATO_NON_DISPONIBILE,
            ];

            if (!isset($stati[$azione])) {
                throw new InvalidArgumentException('Azione ghost kitchen non valida.');
            }

            $ghostKitchen = FPersistentManager::loadGhostKitchen($idGhostKitchen);
            if ($ghostKitchen === null) {
                return $this->esito('Ghost kitchen non trovata', 'La ghost kitchen richiesta non esiste.', false);
            }

            $ghostKitchen->setStato($stati[$azione]);
            $ghostKitchen = FPersistentManager::updateGhostKitchen($ghostKitchen);
            if ($ghostKitchen === false) {
                return $this->esito('Operazione non completata', 'Ghost kitchen non aggiornata.', false);
            }

            return $this->esito('Ghost kitchen aggiornata', 'Lo stato della ghost kitchen e stato aggiornato correttamente.', true);
        } catch (InvalidArgumentException $exception) {
            return $this->esito('Operazione non completata', $exception->getMessage(), false);
        } catch (Throwable $exception) {
            error_log('[CAdminUtenti] ' . $exception->getMessage());
            return $this->esito('Operazione non completata', 'Errore interno durante l aggiornamento ghost kitchen.', false);
        }
    }

    private function mappaGestoriGhostKitchen(array $ghostKitchen): array
    {
        $gestori = [];

        foreach ($ghostKitchen as $item) {
            if (!$item instanceof EGhostKitchen || $item->getIdGestore() === null) {
                continue;
            }

            $idGestore = (int) $item->getIdGestore();
            if (!array_key_exists($idGestore, $gestori)) {
                $gestori[$idGestore] = FPersistentManager::loadGestore($idGestore);
            }
        }

        return $gestori;
    }

    private function filtriUtenti(array $query): array
    {
        $tipo = strtolower(trim((string) ($query['tipo'] ?? 'tutti')));
        if (!in_array($tipo, ['tutti', 'cliente', 'chef', 'gestore', 'ghost_kitchen'], true)) {
            $tipo = 'tutti';
        }

        $stato = strtolower(trim((string) ($query['stato'] ?? 'tutti')));
        if (!in_array($stato, ['tutti', 'attivo', 'sospeso', 'bannato', 'attiva', 'sospesa', 'non_disponibile'], true)) {
            $stato = 'tutti';
        }

        return [
            'q' => trim((string) ($query['q'] ?? '')),
            'tipo' => $tipo,
            'stato' => $stato,
        ];
    }

    private function filtraProfili(array $items, array $filtri, string $tipo): array
    {
        if (($filtri['tipo'] ?? 'tutti') !== 'tutti' && ($filtri['tipo'] ?? '') !== $tipo) {
            return [];
        }

        $q = strtolower((string) ($filtri['q'] ?? ''));
        $stato = (string) ($filtri['stato'] ?? 'tutti');

        return array_values(array_filter($items, static function (object $item) use ($q, $stato): bool {
            if ($stato !== 'tutti' && method_exists($item, 'getStato') && $item->getStato() !== $stato) {
                return false;
            }

            if ($q === '') {
                return true;
            }

            $haystack = '';
            foreach (['getNome', 'getCognome', 'getEmail', 'getTelefono', 'getCitta'] as $method) {
                if (method_exists($item, $method)) {
                    $haystack .= ' ' . (string) $item->{$method}();
                }
            }

            return str_contains(strtolower($haystack), $q);
        }));
    }

    private function esito(string $titolo, string $messaggio, bool $successo): array
    {
        return [
            'titolo' => $titolo,
            'messaggio' => $messaggio,
            'successo' => $successo,
            'ritorno' => '/utenti',
        ];
    }

    private function isAdmin(array $accesso): bool
    {
        $ruoli = $accesso['ruoli'] ?? [];
        return ($accesso['isLogged'] ?? false) === true && (in_array('admin', $ruoli, true) || in_array('amministratore', $ruoli, true));
    }

    private function validaId(int $id, string $messaggio): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException($messaggio);
        }
    }
}
