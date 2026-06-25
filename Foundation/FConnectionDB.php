<?php
declare(strict_types=1);

class FConnectionDB
{
    // Singleton: una sola istanza della classe e una sola connessione PDO riusabile.
    private static ?FConnectionDB $instance = null;  // ?FconectionDB indica che $instance può accettare 2 tipi di valori: un'instanza della classe o un null.
    private ?PDO $connection = null;

    //FConnectionDB garantisce che esiste una sola istanza con una sola connessione PDO riutilizzabile durante la richiesta.

    // Parametri XAMPP locali usati dal progetto demo.
    private string $host = 'localhost';
    private string $dbname = 'GhostKitchen';
    private string $user = 'root';
    private string $password = '';
    private string $charset = 'utf8mb4'; // Supporta caratteri speciali e accenti.

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

    // Punto di accesso globale alla connessione condivisa.
    public static function getInstance(): FConnectionDB
    {
        if (self::$instance === null) { // === confronto stretto
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getConnection(): PDO
    {
        // Se la connessione e gia aperta, viene riutilizzata.
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
                // Le eccezioni rendono gli errori DB gestibili dai blocchi try/catch.
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
        // Impostando null, PHP chiude la connessione PDO quando non e piu referenziata.
        $this->connection = null;
    }
}
