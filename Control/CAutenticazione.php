<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';
require_once __DIR__ . '/../Foundation/FSession.php';

/*
 * Controller dell'autenticazione e del profilo utente.
 * Riceve input dai form, usa FPersistentManager per leggere/salvare su DB
 * e usa FSession per mantenere aggiornati i dati dell'utente loggato.
 * Mostra la pagina di login, verifica le credenziali, effettua logout, visualizza il profilo 
 * dell'utente loggato e gestisce le modifiche al profilo (dati personali, foto, password...)
 */
class CAutenticazione
{
    // Limiti per upload foto profilo: 2 MB e soli formati immagine gestiti dalla view.
    private const MAX_PROFILE_PHOTO_SIZE = 2097152;
    private const PROFILE_PHOTO_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    public function mostraLogin(): array
    {
        // Dati iniziali della pagina login: nessun errore e campo email vuoto.
        return [
            'email' => '',
            'errore' => null,
        ];
    }

    public function profilo(array $accesso, array $query = []): array
    {
        // Il contesto $accesso arriva dal FrontController e descrive l'utente corrente.
        if (($accesso['isLogged'] ?? false) !== true) {
            return [
                'messaggioAccesso' => 'Accedi per visualizzare il tuo profilo.',
                'accesso' => $accesso,
                'section' => 'profilo',
                'isEditing' => false,
            ];
        }

        $section = strtolower(trim((string) ($query['section'] ?? 'profilo')));
        // Whitelist delle sezioni visibili nel profilo: evita tab non previste nella view.
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

        // Un solo endpoint POST gestisce piu azioni del profilo, distinte dal campo "azione".
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
            // Aggiornamento dati anagrafici: carica l'Entity, modifica i campi e salva.
            $utente = FPersistentManager::loadUtente((int) $accesso['idUtente']);
            if ($utente === null) {
                return $this->esitoProfilo('Profilo non aggiornato', 'Profilo utente non trovato.', false);
            }

            $nome = trim((string) ($post['nome'] ?? ''));
            $cognome = trim((string) ($post['cognome'] ?? ''));
            $email = trim((string) ($post['email'] ?? ''));
            // Nome, cognome ed email sono il minimo necessario per mantenere valido il profilo.
            if ($nome === '' || $cognome === '' || $email === '') {
                return $this->esitoProfilo('Profilo non aggiornato', 'Nome, cognome ed email sono obbligatori.', false);
            }

            // I setter dell'Entity applicano eventuali validazioni di dominio.
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
            // La provincia viene accettata solo se esiste tra le sigle italiane previste.
            if ($provincia !== '' && !EUtente::isProvinciaItaliana($provincia)) {
                throw new InvalidArgumentException('Seleziona una provincia valida.');
            }
            $utente->setProvincia($provincia);
            $utente->setNumeroCivico($this->validateProfileText((string) ($post['numeroCivico'] ?? ''), 'Numero civico', 20));
            $utente->setBiografia((string) ($post['biografia'] ?? ''));

            if (FPersistentManager::updateUtente($utente) === false) {
                return $this->esitoProfilo('Profilo non aggiornato', 'Non e stato possibile salvare le informazioni.', false);
            }

            // Dopo il salvataggio aggiorna anche la sessione, cosi navbar/profilo mostrano dati nuovi.
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
            // $_FILES contiene metadati e percorso temporaneo del file appena caricato.
            $file = $files['fotoProfilo'] ?? null;
            if (!is_array($file)) {
                return $this->esitoProfilo('Foto non aggiornata', 'Seleziona una foto valida.', false);
            }

            // Prima salva fisicamente il file, poi aggiorna il percorso nella Entity utente.
            $path = $this->storeProfilePhoto($file);
            $utente = FPersistentManager::loadUtente((int) $accesso['idUtente']);
            if ($utente === null) {
                return $this->esitoProfilo('Foto non aggiornata', 'Profilo utente non trovato.', false);
            }

            $utente->setFotoProfilo($path);
            if (FPersistentManager::updateUtente($utente) === false) {
                return $this->esitoProfilo('Foto non aggiornata', 'Non e stato possibile salvare la foto.', false);
            }

            // La sessione conserva il percorso foto per mostrarlo subito nell'interfaccia.
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
        // Normalizza l'email per confronti coerenti e lascia la password invariata.
        $email = strtolower(trim((string) ($dati['email'] ?? '')));
        $password = (string) ($dati['password'] ?? '');

        if ($email === '' || $password === '') {
            return [
                'successo' => false,
                'email' => $email,
                'errore' => 'Inserisci email e password.',
            ];
        }

        // FPersistentManager verifica credenziali, recupera ruoli e popola FSession.
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
        // Rimuove utente e token CSRF dalla sessione.
        FSession::logout();
    }

    private function aggiornaPassword(array $accesso, array $post): array
    {
        try {
            // Per cambiare password serve l'utente aggiornato dal DB, incluso l'hash salvato.
            $utente = FPersistentManager::loadUtente((int) $accesso['idUtente']);
            if ($utente === null) {
                return $this->esitoProfilo('Password non aggiornata', 'Profilo utente non trovato.', false, '/profilo?section=sicurezza');
            }

            $attuale = (string) ($post['passwordAttuale'] ?? '');
            $nuova = (string) ($post['nuovaPassword'] ?? '');
            $conferma = (string) ($post['confermaPassword'] ?? '');

            // password_verify confronta la password in chiaro con l'hash memorizzato.
            if (!password_verify($attuale, $utente->getPasswordHash())) {
                return $this->esitoProfilo('Password non aggiornata', 'La password attuale non e corretta.', false, '/profilo?section=sicurezza');
            }

            if (strlen($nuova) < 8) {
                return $this->esitoProfilo('Password non aggiornata', 'La nuova password deve contenere almeno 8 caratteri.', false, '/profilo?section=sicurezza');
            }

            if ($nuova !== $conferma) {
                return $this->esitoProfilo('Password non aggiornata', 'La conferma password non corrisponde.', false, '/profilo?section=sicurezza');
            }

            // La nuova password non viene mai salvata in chiaro: si salva solo l'hash.
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
        // Helper condiviso per campi testuali liberi del profilo: trim, lunghezza e caratteri di controllo.
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
            // Permette a un utente esistente di diventare anche chef o gestore.
            $idUtente = (int) ($accesso['idUtente'] ?? 0);
            $ruolo = strtolower(trim((string) ($post['ruolo'] ?? '')));
            $ruoli = $accesso['ruoli'] ?? [];

            // Il ruolo deve essere professionale, non gia presente e legato a un utente valido.
            if ($idUtente <= 0 || !in_array($ruolo, ['chef', 'gestore'], true) || in_array($ruolo, $ruoli, true)) {
                return $this->esitoProfilo('Ruolo non aggiornato', 'Richiesta ruolo non valida.', false);
            }

            if ($ruolo === 'chef') {
                // Per attivare il ruolo chef servono i dati professionali minimi.
                $specializzazione = trim((string) ($post['specializzazione'] ?? ''));
                $tipologiaCucina = trim((string) ($post['tipologiaCucina'] ?? ''));
                if ($specializzazione === '' || $tipologiaCucina === '') {
                    return $this->esitoProfilo('Ruolo chef non attivato', 'Specializzazione e tipologia cucina sono obbligatorie.', false);
                }
                FPersistentManager::addRuoloChef($idUtente, [
                    'biografia' => trim((string) ($post['biografiaChef'] ?? '')) ?: null,
                    'specializzazione' => $specializzazione,
                    'tipologia_cucina' => $tipologiaCucina,
                    'prezzo_base' => max(0, (float) ($post['prezzoBase'] ?? 0)),
                    'anni_esperienza' => max(0, min(EChef::MAX_ANNI_ESPERIENZA, (int) ($post['anniEsperienza'] ?? 0))),
                ]);
            } else {
                // Per il ruolo gestore viene creata anche una ghost kitchen iniziale.
                $nome = trim((string) ($post['nomeGhostKitchen'] ?? ''));
                $descrizione = trim((string) ($post['descrizioneGhostKitchen'] ?? ''));
                $indirizzo = trim((string) ($post['indirizzoGhostKitchen'] ?? ''));
                $citta = trim((string) ($post['cittaGhostKitchen'] ?? ''));
                $cap = trim((string) ($post['capGhostKitchen'] ?? ''));
                if ($nome === '' || $descrizione === '' || $indirizzo === '' || $citta === '' || $cap === '') {
                    return $this->esitoProfilo('Ruolo gestore non attivato', 'Compila i dati principali della ghost kitchen.', false);
                }

                FPersistentManager::addRuoloGestore($idUtente, [
                    'nome' => $nome,
                    'descrizione' => $descrizione,
                    'indirizzo' => $indirizzo,
                    'citta' => $citta,
                    'cap' => $cap,
                    'prezzo_orario' => max(0, (float) ($post['prezzoOrario'] ?? 0)),
                    'capienza' => max(1, (int) ($post['capienza'] ?? 1)),
                    'mq' => max(1, (float) ($post['mq'] ?? 1)),
                ]);
            }

            // Dopo avere modificato i ruoli nel DB, rigenera i dati sessione con il nuovo ruolo attivo.
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
            // La rimozione riguarda solo ruoli professionali, non il ruolo cliente di base.
            $idUtente = (int) ($accesso['idUtente'] ?? 0);
            $ruolo = strtolower(trim((string) ($post['ruolo'] ?? '')));
            $utente = $idUtente > 0 ? FPersistentManager::loadUtente($idUtente) : null;
            $ruoli = $utente !== null ? FPersistentManager::getRuoliUtente($idUtente) : [];
            // Mantiene solo i ruoli rilevanti per decidere se la rimozione e consentita.
            $ruoli = array_values(array_filter($ruoli, static fn (string $item): bool => in_array($item, ['chef', 'gestore', 'cliente'], true)));

            if ($utente === null || !in_array($ruolo, ['chef', 'gestore'], true) || !in_array($ruolo, $ruoli, true)) {
                return $this->esitoProfilo('Ruolo non rimosso', 'Seleziona un ruolo professionale valido.', false);
            }

            if (($post['conferma'] ?? '') !== '1') {
                return $this->esitoProfilo('Ruolo non rimosso', 'Conferma esplicitamente la disattivazione del ruolo.', false);
            }

            // Nel progetto la rimozione e ammessa solo per account che hanno entrambi i ruoli professionali.
            if (!(in_array('chef', $ruoli, true) && in_array('gestore', $ruoli, true))) {
                return $this->esitoProfilo('Ruolo non rimosso', 'La disattivazione e disponibile solo per account con ruolo chef e gestore.', false);
            }

            FPersistentManager::removeRuoloProfessionale($idUtente, $ruolo);

            if ($utente !== null) {
                // Ricarica i ruoli rimasti e sceglie un ruolo attivo ancora valido.
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

    private function storicoPagamenti(int $idUtente): array
    {
        // Prepara una struttura gia pronta per la view: pagamento + etichette leggibili.
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
        // Traduce il pagamento in una descrizione leggibile, recuperando chef o ghost kitchen collegati.
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
            // Se il pagamento non ha data propria, usa la data servizio della prenotazione.
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
        // Recupera lo stato della prenotazione a cui appartiene il pagamento.
        $idPrenotazione = (int) $pagamento->getIdPrenotazione();
        $prenotazione = $pagamento->getTipoPrenotazione() === EPagamento::PRENOTAZIONE_CHEF
            ? FPersistentManager::loadPrenotazioneChef($idPrenotazione)
            : FPersistentManager::loadPrenotazioneGhostKitchen($idPrenotazione);

        return $prenotazione !== null ? $prenotazione->getStato() : '';
    }

    private function storeProfilePhoto(array $file): string
    {
        // Validazione upload: errore PHP, dimensione, estensione e MIME reale del file.
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

        // Le foto profilo vengono salvate sotto public/uploads/profili.
        $uploadDir = dirname(__DIR__) . '/public/uploads/profili';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            throw new RuntimeException('Cartella upload non disponibile.');
        }

        // Nome casuale: evita collisioni e non espone il nome originale caricato dall'utente.
        $fileName = 'profilo_' . (int) random_int(100000, 999999) . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
        $target = $uploadDir . '/' . $fileName;
        if (!move_uploaded_file((string) $file['tmp_name'], $target)) {
            throw new RuntimeException('Salvataggio foto non riuscito.');
        }

        return '/public/uploads/profili/' . $fileName;
    }

    private function esitoProfilo(string $titolo, string $messaggio, bool $successo, string $ritorno = '/profilo'): array
    {
        // Formato unico per le risposte mostrate dal template richiesta_esito.
        return [
            'titolo' => $titolo,
            'messaggio' => $messaggio,
            'successo' => $successo,
            'ritorno' => $ritorno,
        ];
    }
}
