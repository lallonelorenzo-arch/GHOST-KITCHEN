<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

class CContenutiChef
{
    public function aggiornaProfiloWeb(array $accesso, array $post): array
    {
        $idChef = $this->requireChef($accesso);

        try {
            $chef = FPersistentManager::loadChef($idChef);
            if ($chef === null) {
                return $this->esito('Profilo chef', 'Profilo chef non trovato.', false, 'profilo');
            }

            $chef->setBiografia((string) ($post['biografia'] ?? ''));
            $chef->setSpecializzazione((string) ($post['specializzazione'] ?? ''));
            $chef->setTipologiaCucina((string) ($post['tipologiaCucina'] ?? ''));
            $chef->setPrezzoBase((float) ($post['prezzoBase'] ?? 0));
            $chef->setAnniEsperienza((int) ($post['anniEsperienza'] ?? 0));

            if (FPersistentManager::updateChef($chef) === false) {
                return $this->esito('Profilo chef', 'Non e stato possibile aggiornare il profilo.', false, 'profilo');
            }

            return $this->esito('Profilo chef', 'Profilo pubblico aggiornato.', true, 'profilo');
        } catch (InvalidArgumentException $exception) {
            return $this->esito('Profilo chef', $exception->getMessage(), false, 'profilo');
        }
    }

    public function gestisciMenuWeb(array $accesso, array $post): array
    {
        $idChef = $this->requireChef($accesso);
        $azione = strtolower(trim((string) ($post['azione'] ?? '')));
        $idMenu = (int) ($post['idMenu'] ?? 0);

        try {
            if ($azione === 'crea') {
                $menu = new EMenu(
                    null,
                    $idChef,
                    (string) ($post['nome'] ?? ''),
                    (string) ($post['descrizione'] ?? ''),
                    (float) ($post['prezzoPersona'] ?? 0),
                    isset($post['attivo'])
                );
                $this->validaMenu($menu);

                return FPersistentManager::storeMenu($menu) !== false
                    ? $this->esito('Gestione menu', 'Menu creato correttamente.', true, 'profilo#profilo-menu')
                    : $this->esito('Gestione menu', 'Non e stato possibile creare il menu.', false, 'profilo#profilo-menu');
            }

            $menu = $this->menuChef($idMenu, $idChef);
            if ($menu === null) {
                return $this->esito('Gestione menu', 'Menu non trovato o non appartenente al tuo profilo.', false, 'profilo#profilo-menu');
            }

            if ($azione === 'aggiorna') {
                $menu->setNome((string) ($post['nome'] ?? ''));
                $menu->setDescrizione((string) ($post['descrizione'] ?? ''));
                $menu->setPrezzoPersona((float) ($post['prezzoPersona'] ?? 0));
                $menu->setAttivo(isset($post['attivo']));
                $this->validaMenu($menu);
            } elseif ($azione === 'pubblica') {
                $menu->setAttivo(true);
            } elseif ($azione === 'rimuovi') {
                $menu->setAttivo(false);
            } else {
                return $this->esito('Gestione menu', 'Azione menu non valida.', false, 'profilo#profilo-menu');
            }

            return FPersistentManager::updateMenu($menu) !== false
                ? $this->esito('Gestione menu', 'Menu aggiornato correttamente.', true, 'profilo#profilo-menu')
                : $this->esito('Gestione menu', 'Non e stato possibile aggiornare il menu.', false, 'profilo#profilo-menu');
        } catch (InvalidArgumentException $exception) {
            return $this->esito('Gestione menu', $exception->getMessage(), false, 'profilo#profilo-menu');
        } catch (Throwable $exception) {
            error_log('[CContenutiChef] ' . $exception->getMessage());
            return $this->esito('Gestione menu', 'Operazione non completata. Verifica che il nome del menu non sia gia utilizzato.', false, 'profilo#profilo-menu');
        }
    }

    public function gestisciPiattoWeb(array $accesso, array $post): array
    {
        $idChef = $this->requireChef($accesso);
        $azione = strtolower(trim((string) ($post['azione'] ?? '')));
        $idMenu = (int) ($post['idMenu'] ?? 0);
        $menu = $this->menuChef($idMenu, $idChef);
        if ($menu === null) {
            return $this->esito('Gestione piatti', 'Menu non valido.', false, 'profilo#profilo-menu');
        }

        try {
            if ($azione === 'crea') {
                $piatto = new EPiatto(
                    null,
                    $idMenu,
                    (string) ($post['nome'] ?? ''),
                    (string) ($post['categoria'] ?? 'altro'),
                    (string) ($post['descrizione'] ?? ''),
                    (string) ($post['ingredienti'] ?? ''),
                    (string) ($post['allergeni'] ?? ''),
                    (float) ($post['prezzoSupplemento'] ?? 0),
                    (int) ($post['ordineVisualizzazione'] ?? 0)
                );
                $this->validaPiatto($piatto);
                return FPersistentManager::storePiatto($piatto) !== false
                    ? $this->esito('Gestione piatti', 'Piatto aggiunto.', true, 'profilo#profilo-menu')
                    : $this->esito('Gestione piatti', 'Piatto non aggiunto.', false, 'profilo#profilo-menu');
            }

            $idPiatto = (int) ($post['idPiatto'] ?? 0);
            $piatto = $idPiatto > 0 ? FPersistentManager::loadPiatto($idPiatto) : null;
            if ($piatto === null || (int) $piatto->getIdMenu() !== $idMenu) {
                return $this->esito('Gestione piatti', 'Piatto non trovato.', false, 'profilo#profilo-menu');
            }
            if ($azione === 'rimuovi') {
                return FPersistentManager::deletePiatto($idPiatto)
                    ? $this->esito('Gestione piatti', 'Piatto rimosso.', true, 'profilo#profilo-menu')
                    : $this->esito('Gestione piatti', 'Piatto non rimosso.', false, 'profilo#profilo-menu');
            }
            if ($azione !== 'aggiorna') {
                return $this->esito('Gestione piatti', 'Azione non valida.', false, 'profilo#profilo-menu');
            }

            $piatto->setNome((string) ($post['nome'] ?? ''));
            $piatto->setCategoria((string) ($post['categoria'] ?? 'altro'));
            $piatto->setDescrizione((string) ($post['descrizione'] ?? ''));
            $piatto->setIngredienti((string) ($post['ingredienti'] ?? ''));
            $piatto->setAllergeni((string) ($post['allergeni'] ?? ''));
            $piatto->setPrezzoSupplemento((float) ($post['prezzoSupplemento'] ?? 0));
            $piatto->setOrdineVisualizzazione((int) ($post['ordineVisualizzazione'] ?? 0));
            $this->validaPiatto($piatto);

            return FPersistentManager::updatePiatto($piatto) !== false
                ? $this->esito('Gestione piatti', 'Piatto aggiornato.', true, 'profilo#profilo-menu')
                : $this->esito('Gestione piatti', 'Piatto non aggiornato.', false, 'profilo#profilo-menu');
        } catch (InvalidArgumentException $exception) {
            return $this->esito('Gestione piatti', $exception->getMessage(), false, 'profilo#profilo-menu');
        } catch (Throwable $exception) {
            error_log('[CContenutiChef] ' . $exception->getMessage());
            return $this->esito('Gestione piatti', 'Operazione non completata. Verifica ordine e dati del piatto.', false, 'profilo#profilo-menu');
        }
    }

    private function requireChef(array $accesso): int
    {
        if (($accesso['isLogged'] ?? false) !== true || !in_array('chef', $accesso['ruoli'] ?? [], true)) {
            throw new InvalidArgumentException('Serve un account chef per questa operazione.');
        }

        $idChef = (int) ($accesso['idUtente'] ?? 0);
        if ($idChef <= 0) {
            throw new InvalidArgumentException('Account chef non valido.');
        }

        return $idChef;
    }

    private function menuChef(int $idMenu, int $idChef): ?EMenu
    {
        $menu = $idMenu > 0 ? FPersistentManager::loadMenu($idMenu) : null;
        return $menu !== null && (int) $menu->getIdChef() === $idChef ? $menu : null;
    }

    private function validaMenu(EMenu $menu): void
    {
        if ($menu->getNome() === '') {
            throw new InvalidArgumentException('Inserisci il nome del menu.');
        }
        if ($menu->getDescrizione() === '') {
            throw new InvalidArgumentException('Inserisci una descrizione del menu.');
        }
    }

    private function validaPiatto(EPiatto $piatto): void
    {
        if ($piatto->getNome() === '') {
            throw new InvalidArgumentException('Inserisci il nome del piatto.');
        }
    }

    private function esito(string $titolo, string $messaggio, bool $successo, string $tab): array
    {
        return [
            'titolo' => $titolo,
            'messaggio' => $messaggio,
            'successo' => $successo,
            'ritorno' => '/dashboard?ruolo=chef&tab=' . $tab,
        ];
    }
}
