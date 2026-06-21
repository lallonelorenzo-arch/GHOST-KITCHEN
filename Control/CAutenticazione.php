<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';
require_once __DIR__ . '/../Foundation/FSession.php';

class CAutenticazione
{
    private const MAX_PROFILE_PHOTO_SIZE = 2097152;
    private const PROFILE_PHOTO_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    public function mostraLogin(): array
    {
        return [
            'email' => '',
            'errore' => null,
        ];
    }

    public function profilo(array $accesso, array $query = []): array
    {
        if (($accesso['isLogged'] ?? false) !== true) {
            return [
                'messaggioAccesso' => 'Accedi per visualizzare il tuo profilo.',
                'accesso' => $accesso,
                'section' => 'profilo',
                'isEditing' => false,
            ];
        }

        $section = strtolower(trim((string) ($query['section'] ?? 'profilo')));
        if (!in_array($section, ['profilo', 'sicurezza', 'notifiche', 'pagamenti'], true)) {
            $section = 'profilo';
        }

        return [
            'messaggioAccesso' => null,
            'accesso' => $accesso,
            'section' => $section,
            'isEditing' => $section === 'profilo' && (string) ($query['edit'] ?? '') === '1',
            'storicoPagamenti' => $this->storicoPagamenti((int) $accesso['idUtente']),
        ];
    }

    public function aggiornaProfilo(array $accesso, array $post, array $files): array
    {
        if (($accesso['isLogged'] ?? false) !== true) {
            return [
                'titolo' => 'Accesso richiesto',
                'messaggio' => 'Accedi per modificare il profilo.',
                'successo' => false,
                'ritorno' => '/profilo',
            ];
        }

        if ((string) ($post['azione'] ?? '') === 'foto') {
            return $this->aggiornaFotoProfilo($accesso, $files);
        }

        if ((string) ($post['azione'] ?? '') === 'password') {
            return $this->aggiornaPassword($accesso, $post);
        }

        if ((string) ($post['azione'] ?? '') === 'aggiungi_ruolo') {
            return $this->aggiungiRuolo($accesso, $post);
        }

        if ((string) ($post['azione'] ?? '') === 'rimuovi_ruolo') {
            return $this->rimuoviRuolo($accesso, $post);
        }

        try {
            $utente = FPersistentManager::loadUtente((int) $accesso['idUtente']);
            if ($utente === null) {
                return $this->esitoProfilo('Profilo non aggiornato', 'Profilo utente non trovato.', false);
            }

            $nome = trim((string) ($post['nome'] ?? ''));
            $cognome = trim((string) ($post['cognome'] ?? ''));
            $email = trim((string) ($post['email'] ?? ''));
            if ($nome === '' || $cognome === '' || $email === '') {
                return $this->esitoProfilo('Profilo non aggiornato', 'Nome, cognome ed email sono obbligatori.', false);
            }

            $utente->setNome($nome);
            $utente->setCognome($cognome);
            $utente->setEmail($email);
            $utente->setTelefono((string) ($post['telefono'] ?? ''));
            $indirizzo = $this->validateProfileText((string) ($post['indirizzo'] ?? ''), 'Indirizzo', 180);
            $citta = $this->validateProfileText((string) ($post['citta'] ?? ''), 'Città', 120);
            $utente->setIndirizzo($indirizzo);
            $utente->setVia($indirizzo);
            $utente->setCitta($citta);
            $utente->setLocalita($citta);
            $provincia = strtoupper($this->validateProfileText((string) ($post['provincia'] ?? ''), 'Provincia', 2));
            if ($provincia !== '' && !EUtente::isProvinciaItaliana($provincia)) {
                throw new InvalidArgumentException('Seleziona una provincia valida.');
            }
            $utente->setProvincia($provincia);
            $utente->setNumeroCivico($this->validateProfileText((string) ($post['numeroCivico'] ?? ''), 'Numero civico', 20));
            $utente->setBiografia((string) ($post['biografia'] ?? ''));

            if (FPersistentManager::updateUtente($utente) === false) {
                return $this->esitoProfilo('Profilo non aggiornato', 'Non e stato possibile salvare le informazioni.', false);
            }

            FSession::updateUtenteData([
                'nome' => $utente->getNome(),
                'cognome' => $utente->getCognome(),
                'email' => $utente->getEmail(),
                'fotoProfilo' => $utente->getFotoProfilo(),
            ]);

            return $this->esitoProfilo('Profilo aggiornato', 'Le informazioni personali sono state salvate.', true);
        } catch (InvalidArgumentException $exception) {
            return $this->esitoProfilo('Profilo non aggiornato', $exception->getMessage(), false);
        } catch (Throwable $exception) {
            error_log('[CAutenticazione] ' . $exception->getMessage());
            return $this->esitoProfilo('Profilo non aggiornato', 'Errore interno durante il salvataggio.', false);
        }
    }

    public function aggiornaFotoProfilo(array $accesso, array $files): array
    {
        if (($accesso['isLogged'] ?? false) !== true) {
            return [
                'titolo' => 'Accesso richiesto',
                'messaggio' => 'Accedi per modificare il profilo.',
                'successo' => false,
                'ritorno' => '/profilo',
            ];
        }

        try {
            $file = $files['fotoProfilo'] ?? null;
            if (!is_array($file)) {
                return $this->esitoProfilo('Foto non aggiornata', 'Seleziona una foto valida.', false);
            }

            $path = $this->storeProfilePhoto($file);
            $utente = FPersistentManager::loadUtente((int) $accesso['idUtente']);
            if ($utente === null) {
                return $this->esitoProfilo('Foto non aggiornata', 'Profilo utente non trovato.', false);
            }

            $utente->setFotoProfilo($path);
            if (FPersistentManager::updateUtente($utente) === false) {
                return $this->esitoProfilo('Foto non aggiornata', 'Non e stato possibile salvare la foto.', false);
            }

            FSession::setFotoProfilo($path);
            return $this->esitoProfilo('Foto aggiornata', 'La foto profilo e stata aggiornata.', true);
        } catch (InvalidArgumentException $exception) {
            return $this->esitoProfilo('Foto non aggiornata', $exception->getMessage(), false);
        } catch (Throwable $exception) {
            error_log('[CAutenticazione] ' . $exception->getMessage());
            return $this->esitoProfilo('Foto non aggiornata', 'Errore interno durante il caricamento.', false);
        }
    }

    public function login(array $dati): array
    {
        $email = strtolower(trim((string) ($dati['email'] ?? '')));
        $password = (string) ($dati['password'] ?? '');

        if ($email === '' || $password === '') {
            return [
                'successo' => false,
                'email' => $email,
                'errore' => 'Inserisci email e password.',
            ];
        }

        if (!FPersistentManager::login($email, $password)) {
            return [
                'successo' => false,
                'email' => $email,
                'errore' => 'Credenziali non valide o utente non attivo.',
            ];
        }

        return ['successo' => true];
    }

    public function logout(): void
    {
        FSession::logout();
    }

    private function aggiornaPassword(array $accesso, array $post): array
    {
        try {
            $utente = FPersistentManager::loadUtente((int) $accesso['idUtente']);
            if ($utente === null) {
                return $this->esitoProfilo('Password non aggiornata', 'Profilo utente non trovato.', false, '/profilo?section=sicurezza');
            }

            $attuale = (string) ($post['passwordAttuale'] ?? '');
            $nuova = (string) ($post['nuovaPassword'] ?? '');
            $conferma = (string) ($post['confermaPassword'] ?? '');

            if (!password_verify($attuale, $utente->getPasswordHash())) {
                return $this->esitoProfilo('Password non aggiornata', 'La password attuale non e corretta.', false, '/profilo?section=sicurezza');
            }

            if (strlen($nuova) < 8) {
                return $this->esitoProfilo('Password non aggiornata', 'La nuova password deve contenere almeno 8 caratteri.', false, '/profilo?section=sicurezza');
            }

            if ($nuova !== $conferma) {
                return $this->esitoProfilo('Password non aggiornata', 'La conferma password non corrisponde.', false, '/profilo?section=sicurezza');
            }

            $utente->setPasswordHash(password_hash($nuova, PASSWORD_DEFAULT));
            if (FPersistentManager::updateUtente($utente) === false) {
                return $this->esitoProfilo('Password non aggiornata', 'Non e stato possibile salvare la nuova password.', false, '/profilo?section=sicurezza');
            }

            return $this->esitoProfilo('Password aggiornata', 'La password e stata modificata correttamente.', true, '/profilo?section=sicurezza');
        } catch (Throwable $exception) {
            error_log('[CAutenticazione] ' . $exception->getMessage());
            return $this->esitoProfilo('Password non aggiornata', 'Errore interno durante il salvataggio.', false, '/profilo?section=sicurezza');
        }
    }

    private function validateProfileText(string $value, string $label, int $maxLength): string
    {
        $value = trim($value);
        $length = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
        if ($length > $maxLength) {
            throw new InvalidArgumentException($label . ' troppo lungo.');
        }
        if ($value !== '' && preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', $value) === 1) {
            throw new InvalidArgumentException($label . ' contiene caratteri non validi.');
        }

        return $value;
    }

    private function aggiungiRuolo(array $accesso, array $post): array
    {
        try {
            $idUtente = (int) ($accesso['idUtente'] ?? 0);
            $ruolo = strtolower(trim((string) ($post['ruolo'] ?? '')));
            $ruoli = $accesso['ruoli'] ?? [];

            if ($idUtente <= 0 || !in_array($ruolo, ['chef', 'gestore'], true) || in_array($ruolo, $ruoli, true)) {
                return $this->esitoProfilo('Ruolo non aggiornato', 'Richiesta ruolo non valida.', false);
            }

            $connection = FPersistentManager::getConnection();
            if ($ruolo === 'chef') {
                $specializzazione = trim((string) ($post['specializzazione'] ?? ''));
                $tipologiaCucina = trim((string) ($post['tipologiaCucina'] ?? ''));
                if ($specializzazione === '' || $tipologiaCucina === '') {
                    return $this->esitoProfilo('Ruolo chef non attivato', 'Specializzazione e tipologia cucina sono obbligatorie.', false);
                }
                $statement = $connection->prepare('INSERT INTO chef (id_utente, biografia, specializzazione, tipologia_cucina, prezzo_base, anni_esperienza, stato_verifica, valutazione_media, numero_recensioni) VALUES (:id_utente, :biografia, :specializzazione, :tipologia_cucina, :prezzo_base, :anni_esperienza, :stato_verifica, 0.00, 0)');
                $statement->execute([
                    'id_utente' => $idUtente,
                    'biografia' => trim((string) ($post['biografiaChef'] ?? '')) ?: null,
                    'specializzazione' => $specializzazione,
                    'tipologia_cucina' => $tipologiaCucina,
                    'prezzo_base' => max(0, (float) ($post['prezzoBase'] ?? 0)),
                    'anni_esperienza' => max(0, min(EChef::MAX_ANNI_ESPERIENZA, (int) ($post['anniEsperienza'] ?? 0))),
                    'stato_verifica' => EChef::STATO_VERIFICA_IN_ATTESA,
                ]);
            } else {
                $nome = trim((string) ($post['nomeGhostKitchen'] ?? ''));
                $descrizione = trim((string) ($post['descrizioneGhostKitchen'] ?? ''));
                $indirizzo = trim((string) ($post['indirizzoGhostKitchen'] ?? ''));
                $citta = trim((string) ($post['cittaGhostKitchen'] ?? ''));
                $cap = trim((string) ($post['capGhostKitchen'] ?? ''));
                if ($nome === '' || $descrizione === '' || $indirizzo === '' || $citta === '' || $cap === '') {
                    return $this->esitoProfilo('Ruolo gestore non attivato', 'Compila i dati principali della ghost kitchen.', false);
                }

                $connection->prepare('INSERT INTO gestori (id_utente, stato_verifica) VALUES (:id_utente, :stato_verifica)')
                    ->execute(['id_utente' => $idUtente, 'stato_verifica' => EGestore::STATO_VERIFICA_IN_ATTESA]);
                $statement = $connection->prepare('INSERT INTO ghost_kitchen (id_gestore, nome, descrizione, indirizzo, citta, cap, prezzo_orario, capienza, mq, stato, valutazione_media, numero_recensioni) VALUES (:id_gestore, :nome, :descrizione, :indirizzo, :citta, :cap, :prezzo_orario, :capienza, :mq, :stato, 0.00, 0)');
                $statement->execute([
                    'id_gestore' => $idUtente,
                    'nome' => $nome,
                    'descrizione' => $descrizione,
                    'indirizzo' => $indirizzo,
                    'citta' => $citta,
                    'cap' => $cap,
                    'prezzo_orario' => max(0, (float) ($post['prezzoOrario'] ?? 0)),
                    'capienza' => max(1, (int) ($post['capienza'] ?? 1)),
                    'mq' => max(1, (float) ($post['mq'] ?? 1)),
                    'stato' => EGhostKitchen::STATO_SOSPESA,
                ]);
            }

            $utente = FPersistentManager::loadUtente($idUtente);
            if ($utente !== null) {
                FSession::login([
                    'idUtente' => $utente->getIdUtente(),
                    'email' => $utente->getEmail(),
                    'nome' => $utente->getNome(),
                    'cognome' => $utente->getCognome(),
                    'fotoProfilo' => $utente->getFotoProfilo(),
                ], FPersistentManager::getRuoliUtente($idUtente), $ruolo);
            }

            return $this->esitoProfilo('Ruolo aggiunto', 'Il nuovo ruolo e stato creato ed e in attesa delle verifiche richieste.', true);
        } catch (Throwable $exception) {
            error_log('[CAutenticazione] aggiungi ruolo: ' . $exception->getMessage());
            return $this->esitoProfilo('Ruolo non aggiornato', 'Non e stato possibile aggiungere il ruolo richiesto.', false);
        }
    }

    private function rimuoviRuolo(array $accesso, array $post): array
    {
        try {
            $idUtente = (int) ($accesso['idUtente'] ?? 0);
            $ruolo = strtolower(trim((string) ($post['ruolo'] ?? '')));
            $utente = $idUtente > 0 ? FPersistentManager::loadUtente($idUtente) : null;
            $ruoli = $utente !== null ? FPersistentManager::getRuoliUtente($idUtente) : [];
            $ruoli = array_values(array_filter($ruoli, static fn (string $item): bool => in_array($item, ['chef', 'gestore', 'cliente'], true)));

            if ($utente === null || !in_array($ruolo, ['chef', 'gestore'], true) || !in_array($ruolo, $ruoli, true)) {
                return $this->esitoProfilo('Ruolo non rimosso', 'Seleziona un ruolo professionale valido.', false);
            }

            if (($post['conferma'] ?? '') !== '1') {
                return $this->esitoProfilo('Ruolo non rimosso', 'Conferma esplicitamente la disattivazione del ruolo.', false);
            }

            if (!(in_array('chef', $ruoli, true) && in_array('gestore', $ruoli, true))) {
                return $this->esitoProfilo('Ruolo non rimosso', 'La disattivazione e disponibile solo per account con ruolo chef e gestore.', false);
            }

            $connection = FPersistentManager::getConnection();
            $connection->beginTransaction();
            try {
                if ($ruolo === 'chef') {
                    $this->rimuoviRuoloChef($connection, $idUtente);
                } else {
                    $this->rimuoviRuoloGestore($connection, $idUtente);
                }

                $connection->commit();
            } catch (Throwable $exception) {
                if ($connection->inTransaction()) {
                    $connection->rollBack();
                }
                throw $exception;
            }

            if ($utente !== null) {
                $ruoliAggiornati = FPersistentManager::getRuoliUtente($idUtente);
                $ruoloAttivo = in_array('chef', $ruoliAggiornati, true)
                    ? 'chef'
                    : (in_array('gestore', $ruoliAggiornati, true) ? 'gestore' : ($ruoliAggiornati[0] ?? null));
                FSession::login([
                    'idUtente' => $utente->getIdUtente(),
                    'email' => $utente->getEmail(),
                    'nome' => $utente->getNome(),
                    'cognome' => $utente->getCognome(),
                    'fotoProfilo' => $utente->getFotoProfilo(),
                ], $ruoliAggiornati, $ruoloAttivo);
            }

            return $this->esitoProfilo('Ruolo rimosso', 'Il ruolo ' . $ruolo . ' e i dati collegati sono stati rimossi.', true);
        } catch (InvalidArgumentException $exception) {
            return $this->esitoProfilo('Ruolo non rimosso', $exception->getMessage(), false);
        } catch (Throwable $exception) {
            error_log('[CAutenticazione] rimuovi ruolo: ' . $exception->getMessage());
            return $this->esitoProfilo('Ruolo non rimosso', 'Non e stato possibile rimuovere il ruolo selezionato senza compromettere dati storici.', false);
        }
    }

    private function rimuoviRuoloChef(PDO $connection, int $idChef): void
    {
        $prenotazioni = $this->countWhere($connection, 'prenotazioni_chef', 'id_chef = :id', ['id' => $idChef]);
        $recensioni = $this->countWhere($connection, 'recensioni_chef', 'id_chef = :id', ['id' => $idChef]);
        if ($prenotazioni > 0 || $recensioni > 0) {
            throw new InvalidArgumentException('Non posso rimuovere il ruolo chef perche esistono prenotazioni o recensioni collegate.');
        }

        $menuIds = $this->idsWhere($connection, 'menu', 'id_menu', 'id_chef = :id', ['id' => $idChef]);
        $piattoIds = [];
        foreach ($menuIds as $idMenu) {
            $piattoIds = array_merge($piattoIds, $this->idsWhere($connection, 'piatti', 'id_piatto', 'id_menu = :id', ['id' => $idMenu]));
        }

        $this->deleteMediaOwners($connection, 'piatto', $piattoIds);
        $this->deleteMediaOwners($connection, 'menu', $menuIds);
        $this->deleteMediaOwners($connection, 'chef', [$idChef]);
        $connection->prepare('DELETE FROM segnalazioni WHERE tipo_target = :tipo AND id_target = :id')
            ->execute(['tipo' => 'chef', 'id' => $idChef]);
        $connection->prepare('DELETE FROM chef WHERE id_utente = :id')
            ->execute(['id' => $idChef]);
    }

    private function rimuoviRuoloGestore(PDO $connection, int $idGestore): void
    {
        $ghostKitchenIds = $this->idsWhere($connection, 'ghost_kitchen', 'id_ghost_kitchen', 'id_gestore = :id', ['id' => $idGestore]);
        foreach ($ghostKitchenIds as $idGhostKitchen) {
            $prenotazioni = $this->countWhere($connection, 'prenotazioni_ghost_kitchen', 'id_ghost_kitchen = :id', ['id' => $idGhostKitchen]);
            $recensioni = $this->countWhere($connection, 'recensioni_ghost_kitchen', 'id_ghost_kitchen = :id', ['id' => $idGhostKitchen]);
            if ($prenotazioni > 0 || $recensioni > 0) {
                throw new InvalidArgumentException('Non posso rimuovere il ruolo gestore perche una ghost kitchen ha prenotazioni o recensioni collegate.');
            }
        }

        foreach ($ghostKitchenIds as $idGhostKitchen) {
            $this->deleteMediaOwners($connection, 'ghost_kitchen', [$idGhostKitchen]);
            $connection->prepare('DELETE FROM certificazioni WHERE tipo_owner = :tipo AND id_owner = :id')
                ->execute(['tipo' => 'ghost_kitchen', 'id' => $idGhostKitchen]);
            $connection->prepare('DELETE FROM segnalazioni WHERE tipo_target = :tipo AND id_target = :id')
                ->execute(['tipo' => 'ghost_kitchen', 'id' => $idGhostKitchen]);
            $connection->prepare('DELETE FROM ghost_kitchen WHERE id_ghost_kitchen = :id')
                ->execute(['id' => $idGhostKitchen]);
        }

        $connection->prepare('DELETE FROM gestori WHERE id_utente = :id')
            ->execute(['id' => $idGestore]);
    }

    private function countWhere(PDO $connection, string $table, string $where, array $params): int
    {
        $statement = $connection->prepare(sprintf('SELECT COUNT(*) FROM %s WHERE %s', $table, $where));
        $statement->execute($params);
        return (int) $statement->fetchColumn();
    }

    private function idsWhere(PDO $connection, string $table, string $column, string $where, array $params): array
    {
        $statement = $connection->prepare(sprintf('SELECT %s FROM %s WHERE %s', $column, $table, $where));
        $statement->execute($params);
        return array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
    }

    private function deleteMediaOwners(PDO $connection, string $tipoOwner, array $ids): void
    {
        foreach (array_values(array_unique(array_map('intval', $ids))) as $idOwner) {
            if ($idOwner <= 0) {
                continue;
            }
            $connection->prepare('DELETE FROM media WHERE tipo_owner = :tipo_owner AND id_owner = :id_owner')
                ->execute(['tipo_owner' => $tipoOwner, 'id_owner' => $idOwner]);
        }
    }

    private function storicoPagamenti(int $idUtente): array
    {
        $items = [];
        foreach (FPersistentManager::loadPagamentiByUtente($idUtente) as $pagamento) {
            $items[] = [
                'pagamento' => $pagamento,
                'descrizione' => $this->descrizionePagamento($pagamento),
                'data' => $this->dataPagamentoLabel($pagamento),
                'statoPrenotazione' => $this->statoPrenotazionePagamento($pagamento),
            ];
        }

        return $items;
    }

    private function descrizionePagamento(EPagamento $pagamento): string
    {
        $idPrenotazione = (int) $pagamento->getIdPrenotazione();
        if ($pagamento->getTipoPrenotazione() === EPagamento::PRENOTAZIONE_CHEF) {
            $prenotazione = FPersistentManager::loadPrenotazioneChef($idPrenotazione);
            $chef = $prenotazione !== null ? FPersistentManager::loadChef((int) $prenotazione->getIdChef()) : null;
            $nomeChef = $chef !== null ? trim($chef->getNome() . ' ' . $chef->getCognome()) : 'Chef';
            return 'Prenotazione Chef - ' . $nomeChef;
        }

        $prenotazione = FPersistentManager::loadPrenotazioneGhostKitchen($idPrenotazione);
        $ghostKitchen = $prenotazione !== null ? FPersistentManager::loadGhostKitchen((int) $prenotazione->getIdGhostKitchen()) : null;
        $nomeGhostKitchen = $ghostKitchen !== null ? $ghostKitchen->getNome() : 'Ghost Kitchen';
        return 'Ghost Kitchen - ' . $nomeGhostKitchen;
    }

    private function dataPagamentoLabel(EPagamento $pagamento): string
    {
        $rawDate = $pagamento->getDataPagamento();
        if ($rawDate === '') {
            $idPrenotazione = (int) $pagamento->getIdPrenotazione();
            $prenotazione = $pagamento->getTipoPrenotazione() === EPagamento::PRENOTAZIONE_CHEF
                ? FPersistentManager::loadPrenotazioneChef($idPrenotazione)
                : FPersistentManager::loadPrenotazioneGhostKitchen($idPrenotazione);
            $rawDate = $prenotazione !== null ? $prenotazione->getDataServizio() : '';
        }

        if ($rawDate === '') {
            return '';
        }

        $timestamp = strtotime($rawDate);
        return $timestamp !== false ? date('d/m/Y', $timestamp) : $rawDate;
    }

    private function statoPrenotazionePagamento(EPagamento $pagamento): string
    {
        $idPrenotazione = (int) $pagamento->getIdPrenotazione();
        $prenotazione = $pagamento->getTipoPrenotazione() === EPagamento::PRENOTAZIONE_CHEF
            ? FPersistentManager::loadPrenotazioneChef($idPrenotazione)
            : FPersistentManager::loadPrenotazioneGhostKitchen($idPrenotazione);

        return $prenotazione !== null ? $prenotazione->getStato() : '';
    }

    private function storeProfilePhoto(array $file): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('Upload non valido.');
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > self::MAX_PROFILE_PHOTO_SIZE) {
            throw new InvalidArgumentException('La foto supera la dimensione massima consentita.');
        }

        $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if (!in_array($extension, self::PROFILE_PHOTO_EXTENSIONS, true)) {
            throw new InvalidArgumentException('Formato immagine non consentito.');
        }

        $mime = is_file((string) $file['tmp_name']) ? mime_content_type((string) $file['tmp_name']) : '';
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            throw new InvalidArgumentException('Il file caricato non e una immagine valida.');
        }

        $uploadDir = dirname(__DIR__) . '/public/uploads/profili';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            throw new RuntimeException('Cartella upload non disponibile.');
        }

        $fileName = 'profilo_' . (int) random_int(100000, 999999) . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
        $target = $uploadDir . '/' . $fileName;
        if (!move_uploaded_file((string) $file['tmp_name'], $target)) {
            throw new RuntimeException('Salvataggio foto non riuscito.');
        }

        return '/public/uploads/profili/' . $fileName;
    }

    private function esitoProfilo(string $titolo, string $messaggio, bool $successo, string $ritorno = '/profilo'): array
    {
        return [
            'titolo' => $titolo,
            'messaggio' => $messaggio,
            'successo' => $successo,
            'ritorno' => $ritorno,
        ];
    }
}
