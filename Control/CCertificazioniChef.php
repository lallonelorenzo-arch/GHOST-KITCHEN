<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FPersistentManager.php';

class CCertificazioniChef
{
    private const MAX_UPLOAD_SIZE = 5242880;
    private const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];

    public function visualizzaMieCertificazioniWeb(array $accesso): array
    {
        if (!$this->isChef($accesso)) {
            return [
                'accessoRichiesto' => true,
                'messaggioAccesso' => 'Non hai permessi per questa sezione.',
                'certificazioni' => [],
            ];
        }

        return [
            'accesso' => $accesso,
            'certificazioni' => FPersistentManager::loadCertificazioniByChef((int) $accesso['idUtente']),
        ];
    }

    public function caricaCertificazioneWeb(array $accesso, array $post, array $files): array
    {
        if (!$this->isChef($accesso)) {
            return $this->esito('Accesso non consentito', 'Non hai permessi per questa sezione.', false);
        }

        try {
            $tipo = trim((string) ($post['tipo'] ?? ''));
            if ($tipo === '') {
                return $this->esito('Certificazione non caricata', 'Inserisci il tipo di certificazione.', false);
            }

            $file = $files['certificazione'] ?? null;
            if (!is_array($file)) {
                return $this->esito('Certificazione non caricata', 'Seleziona un file valido.', false);
            }

            $stored = $this->storeUploadedFile($file);
            $certificazione = new ECertificazione(
                null,
                (int) $accesso['idUtente'],
                $tipo,
                $stored['nomeFile'],
                $stored['pathFile'],
                ECertificazione::STATO_IN_ATTESA,
                date('Y-m-d'),
                '',
                ''
            );

            if (FPersistentManager::storeCertificazione($certificazione) === false) {
                return $this->esito('Certificazione non caricata', 'Non e stato possibile salvare la certificazione.', false);
            }

            return $this->esito('Certificazione caricata', 'La certificazione e in attesa di revisione.', true);
        } catch (InvalidArgumentException $exception) {
            return $this->esito('Certificazione non caricata', $exception->getMessage(), false);
        } catch (Throwable $exception) {
            error_log('[CCertificazioniChef] ' . $exception->getMessage());
            return $this->esito('Certificazione non caricata', 'Errore interno durante il caricamento.', false);
        }
    }

    private function storeUploadedFile(array $file): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('Upload non valido.');
        }

        if ((int) ($file['size'] ?? 0) <= 0 || (int) $file['size'] > self::MAX_UPLOAD_SIZE) {
            throw new InvalidArgumentException('Il file supera la dimensione massima consentita.');
        }

        $originalName = (string) ($file['name'] ?? '');
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new InvalidArgumentException('Formato file non consentito.');
        }

        $uploadDir = dirname(__DIR__) . '/public/uploads/certificazioni';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            throw new RuntimeException('Cartella upload non disponibile.');
        }

        $safeName = 'cert_' . bin2hex(random_bytes(12)) . '.' . $extension;
        $target = $uploadDir . '/' . $safeName;
        if (!move_uploaded_file((string) $file['tmp_name'], $target)) {
            throw new RuntimeException('Salvataggio file non riuscito.');
        }

        return [
            'nomeFile' => $originalName !== '' ? basename($originalName) : $safeName,
            'pathFile' => '/public/uploads/certificazioni/' . $safeName,
        ];
    }

    private function isChef(array $accesso): bool
    {
        return ($accesso['isLogged'] ?? false) === true && in_array('chef', $accesso['ruoli'] ?? [], true);
    }

    private function esito(string $titolo, string $messaggio, bool $successo): array
    {
        return [
            'titolo' => $titolo,
            'messaggio' => $messaggio,
            'successo' => $successo,
            'ritorno' => '/mie-certificazioni',
        ];
    }
}
