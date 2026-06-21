<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

class CContenutiChef
{
    private const MAX_GALLERY_PHOTO_SIZE = 5242880;
    private const GALLERY_PHOTO_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

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

    public function gestisciMediaWeb(array $accesso, array $post, array $files = []): array
    {
        $idChef = $this->requireChef($accesso);
        $azione = strtolower(trim((string) ($post['azione'] ?? '')));
        $returnTo = $this->safeReturn((string) ($post['returnTo'] ?? '/dashboard?ruolo=chef&tab=profilo#profilo-gallery'));

        try {
            if ($azione === 'rimuovi') {
                $idMedia = (int) ($post['idMedia'] ?? 0);
                $media = $idMedia > 0 ? FPersistentManager::loadMedia($idMedia) : null;
                if ($media === null || $media->getTipoOwner() !== EMedia::OWNER_CHEF || (int) $media->getIdOwner() !== $idChef) {
                    return $this->esito('Galleria chef', 'Foto non trovata o non appartenente al tuo profilo.', false, $returnTo);
                }

                $media->setStato(EMedia::STATO_RIMOSSO);
                return FPersistentManager::updateMedia($media) !== false
                    ? $this->esito('Galleria chef', 'Foto rimossa dalla galleria.', true, $returnTo)
                    : $this->esito('Galleria chef', 'Non e stato possibile rimuovere la foto.', false, $returnTo);
            }

            if ($azione !== 'carica') {
                return $this->esito('Galleria chef', 'Azione media non valida.', false, $returnTo);
            }

            $file = $files['media'] ?? null;
            if (!is_array($file)) {
                return $this->esito('Galleria chef', 'Seleziona una foto valida.', false, $returnTo);
            }

            $stored = $this->storeGalleryPhoto($file, 'chef');
            $ordine = $this->nextMediaOrder(EMedia::OWNER_CHEF, $idChef);
            $media = new EMedia(
                null,
                EMedia::OWNER_CHEF,
                $idChef,
                EMedia::TIPO_MEDIA_FOTO_AMBIENTE,
                $stored['name'],
                $stored['path'],
                $stored['mime'],
                (string) ($post['descrizione'] ?? 'Foto galleria chef'),
                date('Y-m-d H:i:s'),
                $ordine,
                EMedia::STATO_ATTIVO
            );

            return FPersistentManager::storeMedia($media) !== false
                ? $this->esito('Galleria chef', 'Foto aggiunta alla galleria.', true, $returnTo)
                : $this->esito('Galleria chef', 'Non e stato possibile salvare la foto.', false, $returnTo);
        } catch (InvalidArgumentException $exception) {
            return $this->esito('Galleria chef', $exception->getMessage(), false, $returnTo);
        } catch (Throwable $exception) {
            error_log('[CContenutiChef media] ' . $exception->getMessage());
            return $this->esito('Galleria chef', 'Errore interno durante la gestione della galleria.', false, $returnTo);
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

    private function storeGalleryPhoto(array $file, string $folder): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('Upload non valido.');
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > self::MAX_GALLERY_PHOTO_SIZE) {
            throw new InvalidArgumentException('La foto supera la dimensione massima consentita.');
        }

        $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if (!in_array($extension, self::GALLERY_PHOTO_EXTENSIONS, true)) {
            throw new InvalidArgumentException('Formato immagine non consentito.');
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');
        $mime = is_file($tmpName) ? mime_content_type($tmpName) : '';
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            throw new InvalidArgumentException('Il file caricato non e una immagine valida.');
        }

        $uploadDir = dirname(__DIR__) . '/public/uploads/media/' . $folder;
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            throw new RuntimeException('Cartella upload non disponibile.');
        }

        $fileName = $folder . '_' . (int) random_int(100000, 999999) . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
        $target = $uploadDir . '/' . $fileName;
        if (!move_uploaded_file($tmpName, $target)) {
            throw new RuntimeException('Salvataggio foto non riuscito.');
        }

        return [
            'name' => $fileName,
            'path' => '/public/uploads/media/' . $folder . '/' . $fileName,
            'mime' => $mime,
        ];
    }

    private function nextMediaOrder(string $tipoOwner, int $idOwner): int
    {
        $max = -1;
        foreach (FPersistentManager::getMediaByOwner($tipoOwner, $idOwner) as $media) {
            $max = max($max, $media->getOrdine());
        }

        return $max + 1;
    }

    private function safeReturn(string $returnTo): string
    {
        $returnTo = trim($returnTo);
        if ($returnTo === '' || !str_starts_with($returnTo, '/') || str_starts_with($returnTo, '//')) {
            return '/dashboard?ruolo=chef&tab=profilo#profilo-gallery';
        }

        return $returnTo;
    }

    private function esito(string $titolo, string $messaggio, bool $successo, string $tab): array
    {
        $ritorno = str_starts_with($tab, '/') ? $tab : '/dashboard?ruolo=chef&tab=' . $tab;
        return [
            'titolo' => $titolo,
            'messaggio' => $messaggio,
            'successo' => $successo,
            'ritorno' => $ritorno,
        ];
    }
}
