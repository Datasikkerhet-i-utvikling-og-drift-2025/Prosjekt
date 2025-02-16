<?php

namespace models;

use helpers\AuthHelper;
use helpers\InputValidator;
use helpers\Logger;

use DateMalformedStringException;
use DateTime;
use JsonException;
use PDO;
use PDOStatement;


abstract class User
{
    /** @var int|null $id Unique identifier for the user (auto-incremented in the database). */
    public ?int $id {
        get {
            return $this->id;
        }
        set {
            $this->id = $value;
        }
    }

    /** @var string $firstName User's first name. */
    public string $firstName {
        get {
            return $this->firstName;
        }
        set {
            $this->firstName = $value;
        }
    }

    /** @var string $lastName User's last name. */
    public string $lastName {
        get {
            return $this->lastName;
        }
        set {
            $this->lastName = $value;
        }
    }

    /** @var string $fullName User's full name (concatenation of first and last name). */
    public string $fullName {
        get {
            return $this->fullName;
        }
        set {
            $this->fullName = $value;
        }
    }

    /** @var string $email User's email address (used for authentication and communication). */
    public string $email {
        get {
            return $this->email;
        }
        set {
            $this->email = $value;
        }
    }

    /** @var string $password Hashed password used for authentication. */
    public string $password {
        get {
            return $this->password;
        }
        set {
            $this->password = $value;
        }
    }

    /** @var UserRole $role Role of the user, defining their permissions and access levels. */
    public UserRole $role {
        get {
            return $this->role;
        }
        set(UserRole $value) {
            $this->role = $value;
        }
    }

    /** @var string|null $resetToken Optional token for password reset functionality. */
    protected ?string $resetToken {
        get {
            return $this->resetToken;
        }
        set {
            $this->resetToken = $value;
        }
    }

    /** @var DateTime|null $resetTokenCreatedAt Timestamp when the reset token was generated. */
    protected ?DateTime $resetTokenCreatedAt {
        get {
            return $this->resetTokenCreatedAt;
        }
        set {
            $this->resetTokenCreatedAt = $value;
        }
    }

    /** @var DateTime $createdAt Timestamp when the user was created in the system. */
    protected DateTime $createdAt {
        get {
            return $this->createdAt;
        }
        set {
            $this->createdAt = $value;
        }
    }

    // Save a password reset token
    public function savePasswordResetToken($userId, $resetToken)
    {
        $sql = "UPDATE users SET reset_token = :resetToken, reset_token_created_at = NOW() WHERE id = :userId";
        $stmt = $this->pdo->prepare($sql);

        try {
            return $stmt->execute([
                ':resetToken' => $resetToken,
                ':userId' => $userId,
            ]);
        } catch (Exception $e) {
            Logger::error("Failed to save password reset token: " . $e->getMessage());
            return false;
        }
    }

    // Retrieve a user by reset token
    public function getUserByResetToken($resetToken)
    {
        $sql = "SELECT * FROM users WHERE reset_token = :resetToken AND reset_token_created_at >= (NOW() - INTERVAL 1 HOUR)";
        $stmt = $this->pdo->prepare($sql);

        try {
            $stmt->execute([':resetToken' => $resetToken]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            Logger::error("Failed to retrieve user by reset token: " . $e->getMessage());
            return null;
        }
    }

    public function updatePassword($userId, $hashedPassword)  // Endre parameternavn for å være tydeligere
    {
        $stmt->bindValue(':first_name', $this->firstName, PDO::PARAM_STR);
        $stmt->bindValue(':last_name', $this->lastName, PDO::PARAM_STR);
        $stmt->bindValue(':full_name', $this->fullName, PDO::PARAM_STR);
        $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
        $stmt->bindValue(':password', $this->password, PDO::PARAM_STR);
        $stmt->bindValue(':role', $this->role->value, PDO::PARAM_STR);

        $stmt->bindValue(':studyProgram', $this->studyProgram ?? null, isset($this->studyProgram) ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':enrollmentYear', $this->enrollmentYear ?? null, isset($this->enrollmentYear) ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':imagePath', $this->imagePath ?? null, isset($this->imagePath) ? PDO::PARAM_STR : PDO::PARAM_NULL);
    }

}