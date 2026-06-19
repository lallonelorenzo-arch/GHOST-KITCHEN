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

    private function storicoPagamenti(int $idUtente): array
    {
        $items = [];
        foreach (FPersistentManager::loadPagamentiByUtente($idUtente) as $pagamento) {
            $items[] = [
                'pagamento' => $pagamento,
                'descrizione' => $this->descrizionePagamento($pagamento),
                'data' => $this->dataPagamentoLabel($pagamento),
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
