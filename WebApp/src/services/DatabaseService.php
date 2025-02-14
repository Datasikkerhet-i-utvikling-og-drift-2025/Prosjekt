<?php

namespace services;

use helpers\Logger;

use InvalidArgumentException;
use PDO;
use PDOException;
use PDOStatement;

class DatabaseService
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

        $this->connectToDb();
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
                Logger::error('DatabaseService connection failed: ' . $e->getMessage());
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
            $this->bindObjectToSqlStmt($stmt, $bindDataMethod);
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
     * Executes a prepared SQL statement and fetches all results with logging and transaction handling.
     *
     * This method ensures safe execution of a database query and retrieves multiple rows as an associative array.
     * It utilizes `executeSql()` to maintain transaction integrity and logs the outcome.
     *
     * If execution fails, it logs an error and returns an empty array.
     *
     * @param PDOStatement $stmt The prepared statement to execute.
     * @param string|null $loggerMessage Optional log message for debugging.
     *
     * @return array Returns an array of associative arrays containing the fetched rows. Returns an empty array if execution fails or no data is found.
     */
    public function fetchAll(PDOStatement $stmt, ?string $loggerMessage = null,): array
    {
        try {
            $success = $this->executeSql($stmt, $loggerMessage);

            if (!$success) {
                Logger::error("Failed to fetch data: " . ($loggerMessage ?? "No message provided."));
                return [];
            }

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            Logger::success("Successfully fetched data: " . ($loggerMessage ?? "No message provided."));
            return $result;

        } catch (PDOException $e) {
            Logger::error("Fetching data failed: " . $e->getMessage());
            return [];
        }
    }


    /**
     * Executes a prepared SQL statement and fetches a single row with logging and transaction handling.
     *
     * This method ensures safe execution of a database query and retrieves a single row as an associative array.
     * It utilizes `executeSql()` to maintain transaction integrity and logs the outcome.
     *
     * If execution fails or no data is found, it logs an error and returns `null`.
     *
     * @param PDOStatement $stmt The prepared statement to execute.
     * @param string|null $loggerMessage Optional log message for debugging.
     *
     * @return array|null Returns an associative array containing the fetched row, or null if execution fails or no data is found.
     */
    public function fetchSingle(PDOStatement $stmt, ?string $loggerMessage = null): ?array
    {
        try {
            $success = $this->executeSql($stmt, $loggerMessage);

            if (!$success) {
                Logger::error("Failed to fetch single row: " . ($loggerMessage ?? "No message provided."));
                return null;
            }

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                Logger::warning("No data found: " . ($loggerMessage ?? "No message provided."));
                return null;
            }

            Logger::success("Successfully fetched single row: " . ($loggerMessage ?? "No message provided."));
            return $result;

        } catch (PDOException $e) {
            Logger::error("Fetching single row failed: " . $e->getMessage());
            return null;
        }
    }


    /**
     * Executes a prepared SQL statement and fetches a single column value with logging and transaction handling.
     *
     * This method ensures safe execution of a database query and retrieves a specific column value.
     * It utilizes `executeSql()` to maintain transaction integrity and logs the outcome.
     *
     * If execution fails or no data is found, it logs an error and returns `null`.
     *
     * @param PDOStatement $stmt The prepared statement to execute.
     * @param string|null $loggerMessage Optional log message for debugging.
     * @param int $columnIndex The column index to fetch (default is 0, the first column).
     *
     * @return mixed|null Returns the scalar value of the requested column, or null if execution fails or no data is found.
     */
    public function fetchColumn(PDOStatement $stmt, ?string $loggerMessage = null, int $columnIndex = 0): mixed
    {
        try {
            $success = $this->executeSql($stmt, $loggerMessage);

            if (!$success) {
                Logger::error("Failed to fetch column: " . ($loggerMessage ?? "No message provided."));
                return null;
            }

            $result = $stmt->fetchColumn($columnIndex);

            if ($result === false) {
                Logger::warning("No data found for column index $columnIndex: " . ($loggerMessage ?? "No message provided."));
                return null;
            }

            Logger::success("Successfully fetched column value: " . ($loggerMessage ?? "No message provided."));
            return $result;

        } catch (PDOException $e) {
            Logger::error("Fetching column value failed: " . $e->getMessage());
            return null;
        }
    }


    /**
     * Binds data to a prepared PDO statement using a provided callable function.
     *
     * The callable function must accept a PDOStatement and return a modified PDOStatement.
     *
     * @param PDOStatement $stmt The prepared statement to bind data to.
     * @param callable $bindDataMethod A function that binds data and returns a PDOStatement.
     *
     * @return PDOStatement The statement with bound parameters.
     */
    public function bindObjectToSqlStmt(PDOStatement $stmt, callable $bindDataMethod): void
    {
        $result = $bindDataMethod($stmt);

    }


    /**
     * Binds an array of parameters to a prepared PDO statement.
     *
     * This method ensures that each parameter in the `$paramArray` is bound to the corresponding
     * value in `$valueArray`. It throws an exception if the arrays do not have the same length.
     *
     * @param PDOStatement $stmt The prepared statement to which parameters will be bound.
     * @param array $paramArray An array of named parameters (e.g., `[':name', ':email']`).
     * @param array $valueArray An array of values corresponding to each parameter.
     *
     * @throws InvalidArgumentException If the parameter and value arrays do not have the same length.
     */
    public function bindArrayToSqlStmt(PDOStatement $stmt, array $paramArray, array $valueArray): void
    {
        if (count($paramArray) !== count($valueArray)) {
            throw new InvalidArgumentException("Parameter array and value array must have the same length.");
        }

        foreach ($paramArray as $index => $param) {
            $stmt->bindValue($param, $valueArray[$index], PDO::PARAM_STR);
        }
    }


    /**
     * Binds a single parameter to a prepared PDO statement.
     *
     * This method binds a single named parameter to a value in a prepared SQL statement.
     *
     * @param PDOStatement $stmt The prepared statement to which the parameter will be bound.
     * @param string $param The named parameter (e.g., `':email'`).
     * @param mixed $value The value to bind to the parameter.
     */
    public function bindSingleValueToSqlStmt(PDOStatement $stmt, string $param, mixed $value): void
    {
        $stmt->bindValue($param, $value, PDO::PARAM_STR);
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
