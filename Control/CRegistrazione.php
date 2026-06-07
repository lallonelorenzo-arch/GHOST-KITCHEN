<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

class CRegistrazione
{
    private const MAX_CERTIFICAZIONE_SIZE = 5242880;
    private const ALLOWED_CERTIFICAZIONE_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];

    public function mostraRegistrazione(): array
    {
        return $this->formData();
    }

    public function registra(array $post, array $files): array
    {
        try {
            $nome = trim((string) ($post['nome'] ?? ''));
            $cognome = trim((string) ($post['cognome'] ?? ''));
            $email = strtolower(trim((string) ($post['email'] ?? '')));
            $telefono = trim((string) ($post['telefono'] ?? ''));
            $localita = trim((string) ($post['localita'] ?? ''));
            $biografia = trim((string) ($post['biografia'] ?? ''));
            $password = (string) ($post['password'] ?? '');
            $confermaPassword = (string) ($post['confermaPassword'] ?? '');
            $ruoli = $this->ruoliDaPost($post['ruoli'] ?? []);

            $errore = $this->validaBase($nome, $cognome, $email, $telefono, $password, $confermaPassword, $ruoli);
            if ($errore !== null) {
                return $this->formData($post, $errore);
            }

            if (FPersistentManager::emailUtenteExists($email)) {
                return $this->formData($post, 'Esiste gia un account con questa email.');
            }

            $chefData = [];
            $certificazioni = [];
            if (in_array(EUtente::TIPO_CHEF, $ruoli, true)) {
                $chefData = $this->chefData($post, $biografia);
                $erroreChef = $this->validaChefData($chefData);
                if ($erroreChef !== null) {
                    return $this->formData($post, $erroreChef);
                }

                $certificazioni = $this->certificazioniDaUpload($files, (string) ($post['tipoCertificazione'] ?? ''));
                if ($certificazioni === []) {
                    return $this->formData($post, 'Per registrarti come chef devi caricare almeno una certificazione professionale.');
                }
            }

            $ghostKitchenData = [];
            if (in_array(EUtente::TIPO_GESTORE, $ruoli, true)) {
                $ghostKitchenData = $this->ghostKitchenData($post);
                $erroreGhostKitchen = $this->validaGhostKitchenData($ghostKitchenData);
                if ($erroreGhostKitchen !== null) {
                    return $this->formData($post, $erroreGhostKitchen);
                }
            }

            $utente = new EUtente(
                null,
                $nome,
                $cognome,
                $email,
                $password,
                $telefono,
                EUtente::TIPO_UTENTE,
                EUtente::STATO_ATTIVO,
                '',
                $localita,
                $biografia
            );

            $idUtente = FPersistentManager::registraAccount($utente, $ruoli, $chefData, $certificazioni, $ghostKitchenData);
            if ($idUtente === false) {
                return $this->formData($post, 'Non e stato possibile completare la registrazione.');
            }

            return [
                'titolo' => 'Registrazione completata',
                'messaggio' => $this->messaggioSuccesso($ruoli),
                'successo' => true,
                'ritorno' => '/login',
            ];
        } catch (InvalidArgumentException $exception) {
            return $this->formData($post, $exception->getMessage());
        } catch (Throwable $exception) {
            error_log('[CRegistrazione] ' . $exception->getMessage());
            return $this->formData($post, 'Errore interno durante la registrazione. Riprova piu tardi.');
        }
    }

    private function validaBase(string $nome, string $cognome, string $email, string $telefono, string $password, string $confermaPassword, array $ruoli): ?string
    {
        if ($nome === '' || $cognome === '' || $email === '' || $telefono === '') {
            return 'Nome, cognome, email e telefono sono obbligatori.';
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return 'Inserisci una email valida.';
        }

        if (strlen($nome) > 100 || strlen($cognome) > 100) {
            return 'Nome e cognome non possono superare 100 caratteri.';
        }

        if (strlen($telefono) > 30) {
            return 'Il telefono non puo superare 30 caratteri.';
        }

        if ($password === '' || strlen($password) < 8) {
            return 'La password deve contenere almeno 8 caratteri.';
        }

        if (strlen($password) > 128) {
            return 'La password non puo superare 128 caratteri.';
        }

        if ($password !== $confermaPassword) {
            return 'La conferma password non corrisponde.';
        }

        if ($ruoli === []) {
            return 'Seleziona almeno un tipo di account.';
        }

        if (
            in_array(EUtente::TIPO_CLIENTE, $ruoli, true) &&
            (in_array(EUtente::TIPO_CHEF, $ruoli, true) || in_array(EUtente::TIPO_GESTORE, $ruoli, true))
        ) {
            return 'Cliente non puo essere combinato con ruoli professionali. Puoi scegliere solo cliente oppure chef e gestore.';
        }

        return null;
    }

    private function ruoliDaPost(mixed $value): array
    {
        $input = is_array($value) ? $value : [$value];
        $ammessi = [EUtente::TIPO_CLIENTE, EUtente::TIPO_CHEF, EUtente::TIPO_GESTORE];
        $ruoli = [];

        foreach ($input as $ruolo) {
            $ruolo = strtolower(trim((string) $ruolo));
            if (in_array($ruolo, $ammessi, true) && !in_array($ruolo, $ruoli, true)) {
                $ruoli[] = $ruolo;
            }
        }

        return $ruoli;
    }

    private function chefData(array $post, string $biografia): array
    {
        return [
            'biografia' => trim((string) ($post['biografiaChef'] ?? $biografia)),
            'specializzazione' => trim((string) ($post['specializzazione'] ?? '')),
            'tipologiaCucina' => trim((string) ($post['tipologiaCucina'] ?? '')),
            'prezzoBase' => (float) str_replace(',', '.', (string) ($post['prezzoBase'] ?? '0')),
            'anniEsperienza' => (int) ($post['anniEsperienza'] ?? 0),
        ];
    }

    private function ghostKitchenData(array $post): array
    {
        return [
            'nome' => trim((string) ($post['ghostKitchenNome'] ?? '')),
            'descrizione' => trim((string) ($post['ghostKitchenDescrizione'] ?? '')),
            'indirizzo' => trim((string) ($post['ghostKitchenIndirizzo'] ?? '')),
            'citta' => trim((string) ($post['ghostKitchenCitta'] ?? '')),
            'cap' => trim((string) ($post['ghostKitchenCap'] ?? '')),
            'prezzoOrario' => (float) str_replace(',', '.', (string) ($post['ghostKitchenPrezzoOrario'] ?? '0')),
            'capienza' => (int) ($post['ghostKitchenCapienza'] ?? 0),
            'mq' => (float) str_replace(',', '.', (string) ($post['ghostKitchenMq'] ?? '0')),
        ];
    }

    private function validaChefData(array $chefData): ?string
    {
        if ((string) $chefData['specializzazione'] === '' || (string) $chefData['tipologiaCucina'] === '') {
            return 'Per il profilo chef inserisci specializzazione e tipologia di cucina.';
        }

        if (strlen((string) $chefData['specializzazione']) > 150 || strlen((string) $chefData['tipologiaCucina']) > 80) {
            return 'Specializzazione e tipologia cucina sono troppo lunghe.';
        }

        if ((float) $chefData['prezzoBase'] <= 0) {
            return 'Per il profilo chef inserisci un prezzo base valido.';
        }

        if ((float) $chefData['prezzoBase'] > 10000) {
            return 'Il prezzo base chef non puo superare 10.000 euro.';
        }

        if ((int) $chefData['anniEsperienza'] < 0) {
            return 'Gli anni di esperienza non possono essere negativi.';
        }

        if ((int) $chefData['anniEsperienza'] > 80) {
            return 'Gli anni di esperienza chef non possono superare 80.';
        }

        return null;
    }

    private function validaGhostKitchenData(array $data): ?string
    {
        if ($data['nome'] === '' || $data['descrizione'] === '' || $data['indirizzo'] === '' || $data['citta'] === '' || $data['cap'] === '') {
            return 'Per il profilo gestore inserisci i dati principali della ghost kitchen.';
        }

        if (strlen((string) $data['nome']) > 150 || strlen((string) $data['indirizzo']) > 255 || strlen((string) $data['citta']) > 100) {
            return 'Nome, indirizzo o citta della ghost kitchen sono troppo lunghi.';
        }

        if (!preg_match('/^[0-9]{5}$/', (string) $data['cap'])) {
            return 'Inserisci un CAP valido di 5 cifre per la ghost kitchen.';
        }

        if ((float) $data['prezzoOrario'] <= 0 || (float) $data['prezzoOrario'] > 1000) {
            return 'Il prezzo orario della ghost kitchen deve essere tra 1 e 1.000 euro.';
        }

        if ((int) $data['capienza'] <= 0 || (int) $data['capienza'] > 500) {
            return 'La capienza della ghost kitchen deve essere tra 1 e 500 persone.';
        }

        if ((float) $data['mq'] <= 0 || (float) $data['mq'] > 5000) {
            return 'I metri quadri della ghost kitchen devono essere tra 1 e 5.000.';
        }

        return null;
    }

    private function certificazioniDaUpload(array $files, string $tipoCertificazione): array
    {
        $tipoCertificazione = trim($tipoCertificazione) !== '' ? trim($tipoCertificazione) : 'Certificazione professionale';
        $uploads = $this->normalizzaFiles($files['certificazioni'] ?? null);
        $certificazioni = [];

        foreach ($uploads as $file) {
            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $stored = $this->storeUploadedFile($file);
            $certificazioni[] = new ECertificazione(
                null,
                null,
                $tipoCertificazione,
                $stored['nomeFile'],
                $stored['pathFile'],
                ECertificazione::STATO_IN_ATTESA,
                date('Y-m-d H:i:s'),
                '',
                '',
                '',
                ECertificazione::OWNER_CHEF,
                null
            );
        }

        return $certificazioni;
    }

    private function normalizzaFiles(mixed $fileInput): array
    {
        if (!is_array($fileInput)) {
            return [];
        }

        if (!is_array($fileInput['name'] ?? null)) {
            return [$fileInput];
        }

        $items = [];
        foreach ($fileInput['name'] as $index => $name) {
            $items[] = [
                'name' => $name,
                'type' => $fileInput['type'][$index] ?? '',
                'tmp_name' => $fileInput['tmp_name'][$index] ?? '',
                'error' => $fileInput['error'][$index] ?? UPLOAD_ERR_NO_FILE,
                'size' => $fileInput['size'][$index] ?? 0,
            ];
        }

        return $items;
    }

    private function storeUploadedFile(array $file): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('Upload certificazione non valido.');
        }

        if ((int) ($file['size'] ?? 0) <= 0 || (int) $file['size'] > self::MAX_CERTIFICAZIONE_SIZE) {
            throw new InvalidArgumentException('Una certificazione supera la dimensione massima consentita.');
        }

        $originalName = (string) ($file['name'] ?? '');
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_CERTIFICAZIONE_EXTENSIONS, true)) {
            throw new InvalidArgumentException('Formato certificazione non consentito. Usa PDF o immagine.');
        }

        $uploadDir = dirname(__DIR__) . '/public/uploads/certificazioni';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            throw new RuntimeException('Cartella upload certificazioni non disponibile.');
        }

        $safeName = 'reg_cert_' . bin2hex(random_bytes(12)) . '.' . $extension;
        $target = $uploadDir . '/' . $safeName;
        if (!move_uploaded_file((string) $file['tmp_name'], $target)) {
            throw new RuntimeException('Salvataggio certificazione non riuscito.');
        }

        return [
            'nomeFile' => $originalName !== '' ? basename($originalName) : $safeName,
            'pathFile' => '/public/uploads/certificazioni/' . $safeName,
        ];
    }

    private function formData(array $input = [], ?string $errore = null): array
    {
        return [
            'errore' => $errore,
            'input' => $input,
        ];
    }

    private function messaggioSuccesso(array $ruoli): string
    {
        if (in_array(EUtente::TIPO_CHEF, $ruoli, true) || in_array(EUtente::TIPO_GESTORE, $ruoli, true)) {
            return 'Account creato. I profili professionali restano in attesa di verifica amministrativa prima di essere prenotabili.';
        }

        return 'Account cliente creato. Ora puoi accedere con le tue credenziali.';
    }
}
