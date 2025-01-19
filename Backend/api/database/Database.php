<?php

/**
 * Database
 *
 * A simple class for establishing a MySQLi connection using environment
 * variables from docker-compose or the defaults set within the container.
 */
class Database
{
    private $host = 'mysql';      // docker-compose service name for MySQL
    private $dbName = 'database'; // matches the init.sql definition
    private $username = 'admin';  // your DB username
    private $password = 'admin';  // your DB password

    public function getConnection()
    {
        $conn = new mysqli($this->host, $this->username, $this->password, $this->dbName);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        return $conn;
    }
}
