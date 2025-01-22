<?php

namespace models\user;

use DateTime;
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
        $this->userType = $userType ?? UserType::GUEST.toString();
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
    }

    // Denne er her for demo og skal slettes
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

            $db->closeConnection();
            return $conn->lastInsertId();
        } catch (Exception $e) {
            error_log($e->getMessage(), 3, __DIR__ . '/../../logs/error.log');
            throw new Exception("Error saving user");
        }
    }

    public function login()
    {

    }

    public function register()
    {

    }

    public function forgotPassword()
    {

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

    public function getUserType(): string
    {
        return $this->userType;
    }

    public function setUserType(string $userType): void
    {
        $this->userType = $userType;
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
