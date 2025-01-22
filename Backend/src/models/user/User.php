<?php

namespace models\user;

use db\DB;
use Exception;

require_once __DIR__ . '/../../database/DB.php';

// Base class for all users
class User {
    protected int $userId;
    private string $firstName;
    private string $lastName;
    protected string $email;
    protected string $unHashedPassword;
    protected string $userType; // 'student', 'lecturer', 'admin'
    protected string $createdAt;

    public function __construct(
        int $userId,
        string $firstName,
        string $lastName,
        string $email,
        string $unHashedPassword,
        string $userType,
        ?string $createdAt = null
    ) {
        $this->userId = $userId;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->unHashedPassword = $unHashedPassword;
        $this->userType = $userType;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
    }

    public function saveToDB()
    {
        try {
            $db = new DB();
            $conn = $db->getConnection();

            $sql = "INSERT INTO users (email, password_hash, user_type, created_at) 
                    VALUES (:email, :password_hash, :user_type, :created_at)";

            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':email' => $this->email,
                ':password_hash' => password_hash($this->unHashedPassword, PASSWORD_DEFAULT),
                ':user_type' => $this->userType,
                ':created_at' => $this->createdAt,
            ]);

            $this->userId = $conn->lastInsertId();
            $db->closeConnection();
            return $this->userId;
        } catch (Exception $e) {
            error_log($e->getMessage(), 3, __DIR__ . '/../../logs/error.log');
            throw new Exception("Error saving user");
        }
    }
}