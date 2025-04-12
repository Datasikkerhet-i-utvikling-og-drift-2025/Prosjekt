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

    /** @var string $first_name User's first name. */
    public string $first_name {
        get {
            return $this->first_name;
        }
        set {
            $this->first_name = $value;
        }
    }

    /** @var string $last_name User's last name. */
    public string $last_name {
        get {
            return $this->last_name;
        }
        set {
            $this->last_name = $value;
        }
    }

    /** @var string $full_name User's full name (concatenation of first and last name). */
    public string $full_name {
        get {
            return $this->full_name;
        }
        set {
            $this->full_name = $value;
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

    /** @var string|null $reset_token Optional token for password reset functionality. */
    protected ?string $reset_token {
        get {
            return $this->reset_token;
        }
        set {
            $this->reset_token = $value;
        }
    }

    /** @var DateTime|null $reset_token_created_at Timestamp when the reset token was generated. */
    protected ?DateTime $reset_token_created_at {
        get {
            return $this->reset_token_created_at;
        }
        set {
            $this->reset_token_created_at = $value;
        }
    }

    /** @var DateTime $created_at Timestamp when the user was created in the system. */
    protected DateTime $created_at {
        get {
            return $this->created_at;
        }
        set {
            $this->created_at = $value;
        }
    }

    /** @var DateTime $updated_at Timestamp of the last modification of the user record. */
    protected DateTime $updated_at {
        get {
            return $this->updated_at;
        }
        set {
            $this->updated_at = $value;
        }
    }

    /**
     * Constructs a new User instance.
     *
     * This constructor initializes the user properties based on the provided user data array.
     * It ensures that timestamps are set correctly and assigns default values where necessary.
     *
     * @param array $user_data Associative array containing user data with the following keys:<br>
     *        - `id` (int|null) User ID (if null, will be assigned by the database).<br>
     *        - `first_name` (string) User's first name.<br>
     *        - `last_name` (string) User's last name.<br>
     *        - `email` (string) User's email address.<br>
     *        - `password` (string) User's password (hashed or plain text).<br>
     *        - `role` (UserRole) User's assigned role.<br>
     *        - `reset_token` (string|null) Optional reset token for password recovery.<br>
     *        - `reset_token_created_at` (string|null) Timestamp of when the reset token was created.<br>
     *        - `created_at` (string|null) Timestamp when the user was created (defaults to `now`).<br>
     *        - `updated_at` (string|null) Timestamp when the user was last updated (defaults to `now`).
     *
     * @throws DateMalformedStringException If any provided date string cannot be converted to a DateTime object.
     */
    public function __construct(array $user_data)
    {
        $this->id = $user_data['id'] ?? null;
        $this->first_name = InputValidator::sanitizeString($user_data['first_name'] ?? '');
        $this->last_name = InputValidator::sanitizeString($user_data['last_name'] ?? '');
        $this->full_name = $this->first_name . " " . $this->last_name;
        $this->email = isset($user_data['email']) && InputValidator::isValidEmail($user_data['email']) ? $user_data['email'] : '';
        $this->password = $user_data['password'] ?? '';
        $this->role = UserRole::tryFrom($user_data['role']) ?? UserRole::STUDENT;
        $this->reset_token = isset($user_data['reset_token']) ? InputValidator::sanitizeString($user_data['reset_token']) : null;
        $this->reset_token_created_at = isset($user_data['reset_token_created_at']) ? new DateTime($user_data['reset_token_created_at']) : null;
        $this->created_at = new DateTime($user_data['created_at'] ?? 'now');
        $this->updated_at = new DateTime($user_data['updated_at'] ?? 'now');
    }


    /**
     * Binds the user's properties as parameters for a prepared PDO statement.
     *
     * This method ensures that all relevant user attributes are securely bound to a
     * prepared SQL statement before execution, reducing the risk of SQL injection.
     *
     * It also handles specific attributes for `Student` and `Lecturer` subclasses,
     * binding additional fields like `study_program`, `enrollment_year`, and `image_path`.
     *
     * @param PDOStatement $stmt The prepared statement to which user attributes will be bound.
     *
     * @return void
     */
    public function bindUserDataForDbStmt(PDOStatement $stmt): void
    {
        $stmt->bindValue(':first_name', $this->first_name, PDO::PARAM_STR);
        $stmt->bindValue(':last_name', $this->last_name, PDO::PARAM_STR);
        $stmt->bindValue(':full_name', $this->full_name, PDO::PARAM_STR);
        $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
        $stmt->bindValue(':password', $this->password, PDO::PARAM_STR);
        $stmt->bindValue(':role', $this->role->value, PDO::PARAM_STR);

        $stmt->bindValue(':study_program', $this->study_program ?? null, isset($this->study_program) ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':enrollment_year', $this->enrollment_year ?? null, isset($this->enrollment_year) ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':image_path', $this->image_path ?? null, isset($this->image_path) ? PDO::PARAM_STR : PDO::PARAM_NULL);
    }


    /**
     * Converts the user object to an array.
     *
     * @return array
     */
    public function toArray(): array {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'role' => $this->role->value,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s')
        ];
    }

}

