<?php
declare(strict_types=1);

class FSession
{
    private const UTENTE_KEY = 'utente';

    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::start();
        return array_key_exists($key, $_SESSION);
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

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

    public static function login(array $utenteData, array $ruoli, ?string $ruoloAttivo = null): void
    {
        self::start();
        session_regenerate_id(true);

        $ruoli = array_values(array_unique(array_map(
            static fn (string $ruolo): string => strtolower(trim($ruolo)),
            $ruoli
        )));
        $ruoli = array_values(array_filter($ruoli, static fn (string $ruolo): bool => $ruolo !== ''));

        $ruoloAttivo = $ruoloAttivo !== null ? strtolower(trim($ruoloAttivo)) : null;
        if ($ruoloAttivo === null || !in_array($ruoloAttivo, $ruoli, true)) {
            $ruoloAttivo = $ruoli[0] ?? null;
        }

        $_SESSION[self::UTENTE_KEY] = [
            'idUtente' => isset($utenteData['idUtente']) ? (int) $utenteData['idUtente'] : null,
            'email' => isset($utenteData['email']) ? (string) $utenteData['email'] : '',
            'nome' => isset($utenteData['nome']) ? (string) $utenteData['nome'] : '',
            'cognome' => isset($utenteData['cognome']) ? (string) $utenteData['cognome'] : '',
            'ruoli' => $ruoli,
            'ruoloAttivo' => $ruoloAttivo,
        ];
    }

    public static function logout(): void
    {
        self::remove(self::UTENTE_KEY);
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

    private static function getStringField(string $key): ?string
    {
        $value = self::getUtenteField($key);
        return is_string($value) && $value !== '' ? $value : null;
    }

    private static function getUtenteField(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[self::UTENTE_KEY][$key] ?? $default;
    }
}
