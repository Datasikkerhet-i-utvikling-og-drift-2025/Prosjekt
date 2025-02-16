<?php


namespace managers;

use helpers\Logger;
use PDO;
use PDOException;
use PDOStatement;

class DatabaseManager
{
    private string $host;
    private string $dbName;
    private string $username;
    private string $password;
    private ?PDO $pdo = null;
    private ?PDOStatement $stmt = null; // Stores the last prepared statement

    public function __construct()
    {
        $this->host = $_ENV['DB_HOST'];
        $this->dbName = $_ENV['DB_NAME'];
        $this->username = $_ENV['DB_USER'];
        $this->password = $_ENV['DB_PASS'];
    }

    /**
     * Establishes a connection to the database.
     *
     * If the connection is already established, it returns the existing instance.
     * Otherwise, it initializes a new PDO connection.
     *
     * @return PDO The active PDO instance.
     */
    public function connectToDb(): PDO
    {
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
                Logger::error("Database connection failed: " . $e->getMessage());
            }
        }
        return $this->pdo;
    }

    /**
     * Prepares an SQL query for execution.
     *
     * The prepared statement is stored internally and can be executed later.
     * Optionally, a callable function can bind parameters to the statement.
     *
     * @param string $sql The SQL query to prepare.
     * @param callable|null $bindDataMethod A function that binds parameters to the statement.
     * @return bool Returns true if the query was successfully prepared, false otherwise.
     */
    public function prepareStmt(string $sql, ?callable $bindDataMethod = null): bool
    {
        if ($this->pdo === null) {
            $this->connectToDb();
        }

        try {
            $this->stmt = $this->pdo->prepare($sql);

            if ($bindDataMethod !== null) {
                $bindDataMethod($this->stmt);
            }

            return true;
        } catch (PDOException $e) {
            Logger::error("SQL preparation failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Executes the last prepared SQL statement.
     *
     * If no statement has been prepared, it logs an error and returns false.
     * The method also handles transactions, committing on success and rolling back on failure.
     *
     * @param string|null $loggerMessage Optional message for logging the query execution.
     * @return bool Returns true if execution was successful, false otherwise.
     */
    public function executeStmt(?string $loggerMessage = null): bool
    {
        if ($this->pdo === null || $this->stmt === null) {
            Logger::error("Execution failed: No prepared statement or database connection.");
            return false;
        }

        try {
            $this->pdo->beginTransaction();
            $result = $this->stmt->execute();

            if ($result) {
                $this->pdo->commit();
                $loggerMessage && Logger::success("Successfully " . $loggerMessage);
            } else {
                $this->pdo->rollBack();
                $loggerMessage && Logger::error("Failed " . $loggerMessage);
            }

            return $result;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            Logger::error(($loggerMessage ?? "Unknown operation") . " caused error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Executes the last prepared SQL statement and fetches all results.
     *
     * If no statement has been prepared, it logs an error and returns an empty array.
     *
     * @param string|null $loggerMessage Optional message for logging the operation.
     * @return array The fetched results as an associative array, or an empty array on failure.
     */
    public function fetchAll(?string $loggerMessage = null): array
    {
        if ($this->stmt === null) {
            Logger::error("Fetch failed: No prepared statement.");
            return [];
        }

        try {
            $success = $this->executeStmt($loggerMessage);
            if (!$success) { return [];}

            $result = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
            $loggerMessage && Logger::success("Successfully fetched data: " . $loggerMessage);
            return $result;
        } catch (PDOException $e) {
            Logger::error("Fetching data failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Executes the last prepared SQL statement and fetches a single row.
     *
     * If no statement has been prepared, it logs an error and returns null.
     *
     * @param string|null $loggerMessage Optional message for logging the operation.
     * @return array|null The fetched row as an associative array, or null if no data is found.
     */
    public function fetchSingle(?string $loggerMessage = null): ?array
    {
        if ($this->stmt === null) {
            Logger::error("Fetch failed: No prepared statement.");
            return null;
        }

        try {
            $success = $this->executeStmt($loggerMessage);
            if (!$success) { return null; }

            $result = $this->stmt->fetch(PDO::FETCH_ASSOC);
            if (!$result) {
                $loggerMessage && Logger::warning("No data found: " . $loggerMessage);
                return null;
            }

            $loggerMessage && Logger::success("Successfully fetched single row: " . $loggerMessage);
            return $result;
        } catch (PDOException $e) {
            Logger::error("Fetching single row failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Closes the database connection and clears the prepared statement.
     *
     * This ensures that resources are properly released.
     */
    public function closeConnection(): void
    {
        $this->pdo = null;
        $this->stmt = null;
        Logger::info("Database connection closed.");
    }
}

