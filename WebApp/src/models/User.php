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

    /** @var DateTime $updatedAt Timestamp of the last modification of the user record. */
    protected DateTime $updatedAt {
        get {
            return $this->updatedAt;
        }
        set {
            $this->updatedAt = $value;
        }
    }


    /**
     * Constructs a new User instance.
     *
     * This constructor initializes the user properties based on the provided user data array.
     * It ensures that timestamps are set correctly and assigns default values where necessary.
     *
     * @param array $userData Associative array containing user data with the following keys:<br>
     *        - `id` (int|null) User ID (if null, will be assigned by the database).<br>
     *        - `firstName` (string) User's first name.<br>
     *        - `lastName` (string) User's last name.<br>
     *        - `email` (string) User's email address.<br>
     *        - `password` (string) User's password (hashed or plain text).<br>
     *        - `role` (UserRole) User's assigned role.<br>
     *        - `resetToken` (string|null) Optional reset token for password recovery.<br>
     *        - `resetTokenCreatedAt` (string|null) Timestamp of when the reset token was created.<br>
     *        - `createdAt` (string|null) Timestamp when the user was created (defaults to `now`).<br>
     *        - `updatedAt` (string|null) Timestamp when the user was last updated (defaults to `now`).
     *
     * @throws DateMalformedStringException If any provided date string cannot be converted to a DateTime object.
     */
    public function __construct(array $userData)
    {
        $this->id = $userData['id'] ?? null;
        $this->firstName = InputValidator::sanitizeString($userData['firstName'] ?? '');
        $this->lastName = InputValidator::sanitizeString($userData['lastName'] ?? '');
        $this->fullName = $this->firstName . " " . $this->lastName;
        $this->email = isset($userData['email']) && InputValidator::isValidEmail($userData['email']) ? $userData['email'] : '';
        $this->password = AuthHelper::ensurePasswordHashed($userData['password'] ?? '');
        $this->role = UserRole::tryFrom($userData['role']) ?? UserRole::STUDENT;
        $this->resetToken = isset($userData['resetToken']) ? InputValidator::sanitizeString($userData['resetToken']) : null;
        $this->resetTokenCreatedAt = isset($userData['resetTokenCreatedAt']) ? new DateTime($userData['resetTokenCreatedAt']) : null;
        $this->createdAt = new DateTime($userData['createdAt'] ?? 'now');
        $this->updatedAt = new DateTime($userData['updatedAt'] ?? 'now');
    }


    /**
     * Binds the user's properties as parameters for a prepared PDO statement.
     *
     * This method ensures that all relevant user attributes are securely bound to a
     * prepared SQL statement before execution, reducing the risk of SQL injection.
     *
     * It also handles specific attributes for `Student` and `Lecturer` subclasses,
     * binding additional fields like `studyProgram`, `enrollmentYear`, and `imagePath`.
     *
     * @param PDOStatement $stmt The prepared statement to which user attributes will be bound.
     *
     * @return void
     */
    protected function bindUserDataForDbStmt(PDOStatement $stmt): void
    {
        $stmt->bindValue(':firstName', $this->firstName, PDO::PARAM_STR);
        $stmt->bindValue(':lastName', $this->lastName, PDO::PARAM_STR);
        $stmt->bindValue(':fullName', $this->fullName, PDO::PARAM_STR);
        $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
        $stmt->bindValue(':password', $this->password, PDO::PARAM_STR);
        $stmt->bindValue(':role', $this->role->value, PDO::PARAM_STR);

        $stmt->bindValue(':studyProgram', $this->studyProgram ?? null, isset($this->studyProgram) ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':enrollmentYear', $this->enrollmentYear ?? null, isset($this->enrollmentYear) ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':imagePath', $this->imagePath ?? null, isset($this->imagePath) ? PDO::PARAM_STR : PDO::PARAM_NULL);
    }

    /**
     * Converts the user object to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'fullName' => $this->fullName,
            'email' => $this->email,
            'role' => $this->role->value,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s')
        ];
    }
}


