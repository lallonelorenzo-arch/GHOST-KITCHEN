<?php
declare(strict_types=1);

class FConnectionDB
{
    private static ?FConnectionDB $instance = null;
    private ?PDO $connection = null;

    private string $host = 'localhost';
    private string $dbname = 'GhostKitchen';
    private string $user = 'root';
    private string $password = '';
    private string $charset = 'utf8mb4';

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public function __wakeup(): void
    {
        throw new RuntimeException('Cannot unserialize singleton');
    }

    public static function getInstance(): FConnectionDB
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getConnection(): PDO
    {
        if ($this->connection !== null) {
            return $this->connection;
        }

        // Configurazione predefinita per ambiente XAMPP locale.
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $this->host,
            $this->dbname,
            $this->charset
        );

        try {
            $this->connection = new PDO($dsn, $this->user, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Errore durante la connessione al database GhostKitchen.',
                0,
                $exception
            );
        }

        return $this->connection;
    }

    public function closeConnection(): void
    {
        $this->connection = null;
    }
}
