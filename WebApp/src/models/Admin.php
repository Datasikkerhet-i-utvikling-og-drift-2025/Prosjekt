<?php

namespace models;

use helpers\Logger;
use PDO;
use PDOException;
use PDOStatement;

class Admin extends User
{
    public function __construct(array $userData)
    {
        parent::__construct($userData);
    }

    /**
     * Binds the user's properties as parameters for a prepared PDO statement.
     *
     * This method ensures that all relevant user attributes are securely bound to a
     * prepared SQL statement before execution, reducing the risk of SQL injection.
     *
     * @param PDOStatement $stmt The prepared statement to which user attributes will be bound.
     *
     * @return void
     */
    public function bindUserDataForDbStmt(PDOStatement $stmt): void
    {
        parent::bindUserDataForDbStmt($stmt);
    }
}