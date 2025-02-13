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

    /**
     * Establishes a connection to the database using PDO.
     *
     * This method initializes a PDO connection using credentials stored in environment variables.
     * It ensures that the connection is persistent and enables error handling for better debugging.
     *
     * If a connection already exists, it returns the existing instance to prevent redundant connections.
     * If the connection fails, an error is logged, and the method attempts to handle the failure gracefully.
     *
     * @return PDO The established PDO connection instance.
     */
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
     * Prepares an SQL statement for execution and optionally binds parameters using a callable method.
     *
     * This method prepares an SQL query and allows an optional callable method to bind parameters.
     * It ensures that SQL execution is safe and reduces the risk of SQL injection.
     *
     * @param string $sql The SQL query to prepare.
     * @param callable|null $bindDataMethod Optional function to bind parameters to the statement.
     *
     * @return PDOStatement The prepared statement, ready for execution.
     */
    public function prepareSql(string $sql, ?callable $bindDataMethod = null) : PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);

        if ($bindDataMethod !== null) {
            $bindDataMethod($stmt);
        }

        return $stmt;
    }

    /**
     * Executes a prepared SQL statement with transaction handling, error logging, and rollback on failure.
     *
     * @param PDOStatement $stmt The prepared statement to execute.
     * @param string|null $loggerMessage Optional log message for debugging.
     * @return bool Returns true if the query was successful, false if it failed.
     */
    public function executeSql(PDOStatement $stmt, ?string $loggerMessage = null): bool
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

    /**
     * Combines SQL preparation and execution in a single method with optional parameter binding.
     *
     * This method first prepares the SQL statement and then executes it. If a callable method is provided,
     * it is used to bind parameters before execution. The method also supports transaction handling,
     * ensuring data consistency.
     *
     * @param string $sql The SQL query to prepare.
     * @param PDOStatement $stmt The prepared statement to execute.
     * @param string|null $loggerMessage Optional log message for debugging.
     * @param callable|null $bindDataMethod Optional function to bind parameters before execution.
     *
     * @return bool Returns true if the query was successful, false otherwise.
     */
    public function prepareAndExecuteSql(string $sql, PDOStatement $stmt, ?string $loggerMessage = null, ?callable $bindDataMethod = null): bool
    {
        $this->prepareSql($sql, $bindDataMethod);
        return $this->executeSql($stmt, $loggerMessage);
    }

    /**
     * Closes the database connection by setting the PDO instance to null.
     *
     * This method is used to explicitly close the database connection when it is no longer needed.
     * Setting `$this->pdo` to `null` ensures that the connection is closed, freeing up resources.
     */
    public function closeConnection(): void
    {
        $this->pdo = null;
    }
}
