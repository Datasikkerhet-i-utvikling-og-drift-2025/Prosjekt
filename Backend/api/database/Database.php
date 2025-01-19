<?php

/**
 * Class Database
 *
 * Handles the MySQL database for the API controllers:
 */
class Database
{
    private $host = 'mysql';
    private $dbName = 'database';
    private $username = 'admin';
    private $password = 'admin';

    public function getConnection()
    {
        $conn = new mysqli($this->host, $this->username, $this->password, $this->dbName);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        return $conn;
    }
}
