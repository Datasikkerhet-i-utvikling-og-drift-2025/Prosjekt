<?php

namespace models\user;

use DateTime;
use db\DB;
use Exception;

require_once __DIR__ . '/../../database/DB.php';

// Base class for all users
class User {
    protected int $userId;
    protected string $email;
    protected string $unHashedPassword;
    protected string $createdAt;
    private string $firstName;
    private string $lastName;

    public function __construct(
        int $userId,
        string $email,
        string $unHashedPassword,
        ?string $createdAt = null,
        string $firstName,
        string $lastName,
    ) {
        $this->userId = $userId;
        $this->email = $email;
        $this->unHashedPassword = $unHashedPassword;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    // Denne er her for demo og skal slettes
    public function saveToDB()
    {
        try {
            $db = new DB();
            $conn = $db->getConnection();

            $sql = "INSERT INTO users (email, password_hash, created_at, first_name, last_name) 
                    VALUES (:email, :password_hash, :created_at, :first_name, :last_name)";

            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':email' => $this->email,
                ':password_hash' => password_hash($this->unHashedPassword, PASSWORD_DEFAULT),
                ':created_at' => $this->createdAt,
                ':first_name' => $this->firstName,
                ':last_name' => $this->lastName,
            ]);

            $db->closeConnection();
            return $conn->lastInsertId();
        } catch (Exception $e) {
            error_log($e->getMessage(), 3, __DIR__ . '/../../logs/error.log');
            throw new Exception("Error saving user");
        }
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getUnHashedPassword(): string
    {
        return $this->unHashedPassword;
    }

    public function setUnHashedPassword(string $unHashedPassword): void
    {
        $this->unHashedPassword = $unHashedPassword;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
