<?php



class DB
{
    private string $host;
    private string $dbName;
    private string $username;
    private string $password;
    private ?PDO $connection = null;

    public function __construct()
    {
        // Load environment variables
        #$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        #$dotenv->load();

        // Set properties from .env or fallback defaults
        #$this->host = $_ENV['DB_HOST'] ?? 'mysql';
        #$this->dbName = $_ENV['DB_NAME'] ?? 'database';
        #$this->username = $_ENV['DB_USER'] ?? 'admin';
        #$this->password = $_ENV['DB_PASS'] ?? 'admin';
        $this->host = 'mysql';
        $this->dbName = 'database';
        $this->username = 'admin';
        $this->password = 'admin';
    }

    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_PERSISTENT => true,
                ];

                $this->connection = new PDO($dsn, $this->username, $this->password, $options);
            } catch (PDOException $e) {
                error_log($e->getMessage(), 3, __DIR__ . '/../logs/error.log');
                die("Database connection failed. Please try again later.");
            }
        }

        return $this->connection;
    }

    public function closeConnection(): void
    {
        $this->connection = null;
    }
}
