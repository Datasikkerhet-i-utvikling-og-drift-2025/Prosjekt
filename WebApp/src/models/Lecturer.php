<?php

namespace models;

use DateMalformedStringException;
use PDO;
use PDOStatement;

class Lecturer extends User
{
    public string $imagePath {
        get {
            return $this->imagePath;
        }
        set {
            $this->imagePath = $value;
        }
    }

    /**
     * Constructs a new Lecturer instance.
     *
     * This constructor initializes a lecturer object with general user attributes
     * from the `User` class and adds lecturer-specific attributes like imagePath.
     *
     * @param array $userData Associative array containing lecturer data.<br>
     *        - `id` (int|null) User ID (if null, assigned by the database).<br>
     *        - `firstName` (string) lecturer's first name.<br>
     *        - `lastName` (string) lecturer's last name.<br>
     *        - `email` (string) lecturer's email address.<br>
     *        - `password` (string) lecturer's password (hashed or plaintext).<br>
     *        - `role` (UserRole) The lecturer's role in the system.<br>
     *        - `resetToken` (string|null) Optional reset token for password recovery.<br>
     *        - `resetTokenCreatedAt` (string|null) Timestamp of password reset request.<br>
     *        - `createdAt` (string|null) Timestamp when the lecturer account was created.<br>
     *        - `updatedAt` (string|null) Timestamp of last lecturer account update.<br>
     *        - `imagePath` (string) The path to where the profile picture is stored.<br>
     *
     * @throws DateMalformedStringException
     */
    public function __construct(array $userData)
    {
        parent::__construct($userData);
        $this->imagePath = $userData['imagePath'] ?? "";
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

        $stmt->bindValue(':imagePath', $this->imagePath, PDO::PARAM_STR );

        $stmt->bindValue(':studyProgram', $this->studyProgram ?? null, isset($this->studyProgram) ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':enrollmentYear', $this->enrollmentYear ?? null, isset($this->enrollmentYear) ? PDO::PARAM_INT : PDO::PARAM_NULL);
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
            'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s'),
            'imagePath' => $this->imagePath
        ];
    }

}

