<?php


namespace managers;

use helpers\GrayLogger;
use helpers\Logger;
use InvalidArgumentException;
use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

class DatabaseManager
{
    private string $host;
    private string $dbName;

    private string $username;
    private string $password;
    
    private ?PDO $pdo = null;
    private ?PDOStatement $stmt = null; // Stores the last prepared statement

    private GrayLogger $logger;

    public function __construct(string $role)
    {
        $this->host = $_ENV['DB_HOST'];
        $this->dbName = $_ENV['DB_NAME'];

        switch($role) {
            case 'user':
                $this->username = $_ENV['DB_USER'] ?? throw new RuntimeException('DB_USER not set');
                $this->password = $_ENV['DB_USER_PASS'] ?? throw new RuntimeException('DB_USER_PASS not set');
                break;
            case 'lecturer':
                $this->username = $_ENV['DB_LECTURER_USER'] ?? throw new RuntimeException('DB_LECTURER_USER not set');
                $this->password = $_ENV['DB_LECTURER_PASS'] ?? throw new RuntimeException('DB_LECTURER_PASS not set');
                break;
            case 'student':
                $this->username = $_ENV['DB_STUDENT_USER'] ?? throw new RuntimeException('DB_STUDENT_USER not set');
                $this->password = $_ENV['DB_STUDENT_PASS'] ?? throw new RuntimeException('DB_STUDENT_PASS not set');
                break;
            case 'guest':
                $this->username = $_ENV['DB_GUEST_USER'] ?? throw new RuntimeException('DB_GUEST_USER not set');
                $this->password = $_ENV['DB_GUEST_PASS'] ?? throw new RuntimeException('DB_GUEST_PASS not set');
                break;
            default:
                throw new InvalidArgumentException("Invalid role: $role");
        }

        $this->logger = GrayLogger::getInstance();
        //$this->logger->debug("ENV vars loaded for role: $role", [
        //    'DB_HOST' => $_ENV['DB_HOST'] ?? 'missing',
        //    'DB_NAME' => $_ENV['DB_NAME'] ?? 'missing',
        //    'username' => $this->username ?? 'null',
        //    'password' => $this->password ?? 'null'
        //]);
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
                //$this->logger->info("Connected to database");
            } catch (PDOException $e) {
                $this->logger->error("Database connection failed: " . $e->getMessage());
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
            $this->logger->error("SQL preparation failed: " . $e->getMessage());
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
    public function executeTransaction(?string $loggerMessage = null): bool
    {
        if ($this->pdo === null || $this->stmt === null) {
            $this->logger->error("Execution failed: No prepared statement or database connection.");
            return false;
        }

        try {
            $this->pdo->beginTransaction();
            $result = $this->stmt->execute();
            $this->logger->debug("Result status: " . $result);
            $this->stmt->closeCursor();

            if ($result) {
                $this->pdo->commit();
                $loggerMessage && $this->logger->info("Successfully " . $loggerMessage);
            } else {
                $this->pdo->rollBack();
                $loggerMessage && $this->logger->error("Failed " . $loggerMessage);
            }

            return $result;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            $this->logger->error(($loggerMessage ?? "Unknown operation") . " caused error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Executes a raw SQL statement without using transactions.
     *
     * This is useful for simple operations like INSERT, UPDATE, DELETE or DDL statements
     * where transaction management is not necessary.
     *
     * @param string $sql The SQL statement to execute.
     * @param callable|null $bindDataMethod Optional callback to bind values to the statement.
     * @param string|null $loggerMessage Optional message for logging success/failure.
     * @return bool True if the statement executed successfully, false otherwise.
     */
    public function executeDirect(string $sql, ?callable $bindDataMethod = null, ?string $loggerMessage = null): bool
    {
        if ($this->pdo === null) {
            $this->connectToDb();
        }

        try {
            $stmt = $this->pdo->prepare($sql);

            if ($bindDataMethod !== null) {
                $bindDataMethod($stmt);
            }

            $success = $stmt->execute();

            if ($success) {
                $loggerMessage && $this->logger->info("Successfully executed: " . $loggerMessage);
            } else {
                $loggerMessage && $this->logger->error("Execution failed: " . $loggerMessage);
            }

            return $success;
        } catch (PDOException $e) {
            $this->logger->error(($loggerMessage ?? "Direct SQL execution") . " failed: " . $e->getMessage());
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
            $this->logger->error("Fetch failed: No prepared statement.");
            return [];
        }

        try {
            $success = $this->stmt->execute();
            $this->logger->debug("Result status: " . $success);
            if (!$success) { return [];}

            $result = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->stmt->closeCursor();
            $loggerMessage && $this->logger->info("Successfully fetched data: " . $loggerMessage);
            $this->logger->debug("Fetch result: " . $result);
            return $result;
        } catch (PDOException $e) {
            $this->logger->error("Fetching data failed: " . $e->getMessage());
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
            $this->logger->error("Fetch failed: No prepared statement.");
            return null;
        }

        try {
            $success = $this->stmt->execute();
            $this->logger->debug("Result status: " . $success);

            $result = $this->stmt->fetch(PDO::FETCH_ASSOC);
            $this->stmt->closeCursor();

            if ($result === false || $result === null) {
                $loggerMessage && $this->logger->warning("No data found: " . $loggerMessage);
                return null;
            }

            $this->logger->debug("Fetch result", ['data' => $result]);
            $loggerMessage && $this->logger->info("Successfully fetched single row: " . $loggerMessage);
            return $result;
        } catch (PDOException $e) {
            $this->logger->error("Fetching single row failed: " . $e->getMessage());
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
        $this->logger->info("Database connection closed.");
    }
}

