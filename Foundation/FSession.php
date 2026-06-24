<?php
declare(strict_types=1);

class FSession
{
    private const UTENTE_KEY = 'utente';        // Dati dell'utente loggato (idUtente, email, nome, cognome, fotoProfilo...)
    private const CSRF_KEY = 'csrf_tokens';     // Token CSRF per protezione contro attacchi CSRF nei form POST.

    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    // Salva un valore nella sessione con la chiave specificata.
    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    // Recupera un valore dalla sessione con la chiave specificata, oppure restituisce un valore di default se la chiave non esiste.
    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    // Verifica se esiste un valore nella sessione con la chiave specificata.
    public static function has(string $key): bool
    {
        self::start();
        return array_key_exists($key, $_SESSION);
    }

    // Rimuove un valore dalla sessione con la chiave specificata.
    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    // Distrugge la sessione corrente, rimuovendo tutti i dati e i cookie associati.
    public static function destroy(): void
    {
        self::start();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    // Effettua il login dell'utente, salvando i dati nella sessione.
    public static function login(array $utenteData, array $ruoli, ?string $ruoloAttivo = null): void
    {
        self::start();
        session_regenerate_id(true);
        unset($_SESSION[self::CSRF_KEY]);

        $ruoli = array_values(array_unique(array_map(
            static fn (string $ruolo): string => strtolower(trim($ruolo)),      // Normalizza i ruoli in minuscolo e rimuove eventuali duplicati
            $ruoli
        )));
        $ruoli = array_values(array_filter($ruoli, static fn (string $ruolo): bool => $ruolo !== ''));      // Rimuove eventuali ruoli vuoti

        $ruoloAttivo = $ruoloAttivo !== null ? strtolower(trim($ruoloAttivo)) : null;
        if ($ruoloAttivo === null || !in_array($ruoloAttivo, $ruoli, true)) {
            $ruoloAttivo = $ruoli[0] ?? null;
        }

        $_SESSION[self::UTENTE_KEY] = [
            'idUtente' => isset($utenteData['idUtente']) ? (int) $utenteData['idUtente'] : null,
            'email' => isset($utenteData['email']) ? (string) $utenteData['email'] : '',
            'nome' => isset($utenteData['nome']) ? (string) $utenteData['nome'] : '',
            'cognome' => isset($utenteData['cognome']) ? (string) $utenteData['cognome'] : '',
            'fotoProfilo' => isset($utenteData['fotoProfilo']) ? (string) $utenteData['fotoProfilo'] : '',
            'ruoli' => $ruoli,
            'ruoloAttivo' => $ruoloAttivo,
        ];
    }

    // Effettua il logout dell'utente, rimuovendo i dati dalla sessione.
    public static function logout(): void
    {
        self::remove(self::UTENTE_KEY);
        self::remove(self::CSRF_KEY);
    }

    public static function isLogged(): bool
    {
        self::start();
        return isset($_SESSION[self::UTENTE_KEY]['idUtente'])
            && is_int($_SESSION[self::UTENTE_KEY]['idUtente'])
            && $_SESSION[self::UTENTE_KEY]['idUtente'] > 0;
    }

    public static function getIdUtente(): ?int
    {
        $idUtente = self::getUtenteField('idUtente');
        return is_int($idUtente) ? $idUtente : null;
    }

    public static function getEmail(): ?string
    {
        return self::getStringField('email');
    }

    public static function getNome(): ?string
    {
        return self::getStringField('nome');
    }

    public static function getCognome(): ?string
    {
        return self::getStringField('cognome');
    }

    public static function getRuoli(): array
    {
        $ruoli = self::getUtenteField('ruoli', []);
        return is_array($ruoli) ? $ruoli : [];
    }

    public static function getFotoProfilo(): ?string
    {
        return self::getStringField('fotoProfilo');
    }

    public static function setFotoProfilo(string $fotoProfilo): void
    {
        self::start();
        if (isset($_SESSION[self::UTENTE_KEY])) {
            $_SESSION[self::UTENTE_KEY]['fotoProfilo'] = trim($fotoProfilo);
        }
    }

    public static function updateUtenteData(array $utenteData): void
    {
        self::start();
        if (!isset($_SESSION[self::UTENTE_KEY])) {
            return;
        }

        foreach (['email', 'nome', 'cognome', 'fotoProfilo'] as $key) {
            if (array_key_exists($key, $utenteData)) {
                $_SESSION[self::UTENTE_KEY][$key] = (string) $utenteData[$key];
            }
        }
    }

    public static function hasRuolo(string $ruolo): bool
    {
        return in_array(strtolower(trim($ruolo)), self::getRuoli(), true);
    }

    public static function getRuoloAttivo(): ?string
    {
        return self::getStringField('ruoloAttivo');
    }

    public static function setRuoloAttivo(string $ruolo): bool
    {
        self::start();
        $ruolo = strtolower(trim($ruolo));

        if (!self::hasRuolo($ruolo)) {
            return false;
        }

        $_SESSION[self::UTENTE_KEY]['ruoloAttivo'] = $ruolo;
        return true;
    }

    public static function requireLogin(): bool
    {
        return self::isLogged();
    }

    // Genera o recupera un token CSRF per il contesto specificato.
    public static function csrfToken(string $scope): string
    {
        self::start();
        $scope = trim($scope);  // Normalizzazione del nome/etichetta del form a cui devo applicare il token CSRF (es. "login", "registrazione", "modifica-profilo", ecc.)
        if ($scope === '') {
            throw new InvalidArgumentException('Ambito CSRF non valido.');
        }

        $token = $_SESSION[self::CSRF_KEY][$scope] ?? null;
        if (!is_string($token) || strlen($token) !== 64) {
            $token = bin2hex(random_bytes(32));     // Se il token non esiste o non è valido, generane uno nuovo sicuro.
            $_SESSION[self::CSRF_KEY][$scope] = $token;
        }

        return $token;
    }

    // Verifica se un token CSRF è valido per il contesto specificato.
    public static function verifyCsrfToken(string $scope, string $token): bool
    {
        self::start();
        $stored = $_SESSION[self::CSRF_KEY][trim($scope)] ?? null;
        return is_string($stored) && $stored !== '' && hash_equals($stored, trim($token));
    }

    // Prende un campo dalla sessione solo se è una stringa non vuota, altrimenti restituisce null.
    private static function getStringField(string $key): ?string
    {
        $value = self::getUtenteField($key);
        return is_string($value) && $value !== '' ? $value : null;
    }

    // Recupera un campo specifico dell'utente dalla sessione, restituendo un valore di default se il campo non esiste.
    private static function getUtenteField(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[self::UTENTE_KEY][$key] ?? $default;
    }
}
