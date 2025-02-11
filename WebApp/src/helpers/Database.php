<?php

namespace helpers;

use PDO;
use PDOException;
use PDOStatement;

class Database
{
    private string $host;
    private string $dbName;
    private string $username;
    private string $password;
    public ?PDO $pdo = null {
        get {
            return $this->pdo;
        }
    }

    public function __construct()
    {
        $this->host = $_ENV['DB_HOST'];
        $this->dbName = $_ENV['DB_NAME'];
        $this->username = $_ENV['DB_USER'];
        $this->password = $_ENV['DB_PASS'];
    }

    public function connectToDb(): PDO
    {
        Logger::info("Connecting to database");
        if ($this->pdo === null) {
            try {
                $dsn = "mysql:host=$this->host;dbname=$this->dbName;charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_PERSISTENT => true,
                ];

                $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
                Logger::success("Connected to database");
            } catch (PDOException $e) {
                Logger::error('Database connection failed: ' . $e->getMessage());
            }
        }

        return $this->pdo;
    }

    /**
     * Executes a prepared SQL statement with transaction handling, error logging, and rollback on failure.
     *
     * @param PDOStatement $stmt The prepared statement to execute.
     * @param string|null $loggerMessage Optional log message for debugging.
     * @return bool Returns true if the query was successful, false if it failed.
     */
    public function executeStatement(PDOStatement $stmt, ?string $loggerMessage = null): bool
    {
        try {
            $this->pdo->beginTransaction();

            $result = $stmt->execute();

            if ($result) {
                $this->pdo->commit();

                if($loggerMessage !== null) {
                    Logger::success("Successfully " . $loggerMessage);
                }

            } else {
                $this->pdo->rollBack();

                if($loggerMessage !== null) {
                    Logger::error("Failed " . $loggerMessage);
                }

            }

            return $result;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            Logger::error("[Error] " . $loggerMessage . ": " . $e->getMessage());
            return false;
        }
    }

    public function closeConnection(): void
    {
        $this->pdo = null;
    }
}
