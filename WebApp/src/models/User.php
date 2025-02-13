<?php

namespace models;

use DateMalformedStringException;
use DateTime;
use helpers\AuthHelper;
use helpers\InputValidator;
use helpers\Logger;
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
    public function __construct(array $userData) {
        $this->id = $userData['id'] ?? null;
        $this->firstName = InputValidator::sanitizeString($userData['firstName']);
        $this->lastName = InputValidator::sanitizeString($userData['lastName']);
        $this->fullName = $this->firstName . " " . $this->lastName;
        $this->email = InputValidator::sanitizeEmail($userData['email']);
        $this->password = AuthHelper::ensurePasswordHashed($userData['password']);
        $this->role = $userData['role'];
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
    public function bindUserDataForDbStmt(PDOStatement $stmt): void
    {
        $stmt->bindValue(':id', $this->id ?? null, $this->id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':first_name', $this->firstName, PDO::PARAM_STR);
        $stmt->bindValue(':last_name', $this->lastName, PDO::PARAM_STR);
        $stmt->bindValue(':full_name', $this->fullName, PDO::PARAM_STR);
        $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
        $stmt->bindValue(':password', $this->password, PDO::PARAM_STR);
        $stmt->bindValue(':role', $this->role->value, PDO::PARAM_STR);

        $studyProgram = null;
        $studyYear = null;
        $imagePath = null;

        if ($this instanceof Student) {
            $studyProgram = $this->studyProgram;
            $studyYear = $this->enrollmentYear;
        } elseif ($this instanceof Lecturer) {
            $imagePath = $this->imagePath;
        }

        $stmt->bindValue(':studyProgram', $studyProgram, $studyProgram !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':studyYear', $studyYear, $studyYear !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':imagePath', $imagePath, $imagePath !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    }





    // Create a new user
    public function createUser($name, $email, $password, $role, $studyProgram = null, $studyYear = null, $imagePath = null)
    {
        Logger::info("Creating User " . $name . " " . $email . " " . $password . " " . $role . " " . $studyProgram . " " . $studyYear . " " . $imagePath);
        // Validate inputs
        $validationRules = [
            'name' => ['required' => true, 'sanitize' => true, 'min' => 3, 'max' => 100],
            'email' => ['required' => true, 'email' => true],
            'password' => ['required' => true, 'password' => true],
            'role' => ['required' => true],
        ];
        $validation = InputValidator::validateInputs([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role,
        ], $validationRules);

        if (!empty($validation['errors'])) {
            Logger::error("User creation failed: Validation errors - " . json_encode($validation['errors']));
            return false;
        }

        // Ensure the role is valid
        $validRoles = ['student', 'lecturer', 'admin'];
        if (!in_array($role, $validRoles)) {
            Logger::error("Invalid role provided: $role");
            return false;
        }

        // Insert user into the database
        $sql = "INSERT INTO users (name, email, password, role, study_program, study_year, image_path, created_at, updated_at)
                VALUES (:name, :email, :password, :role, :studyProgram, :studyYear, :imagePath, NOW(), NOW())";
        $stmt = $this->pdo->prepare($sql);

        try {
            return $stmt->execute([
                ':name' => InputValidator::sanitizeString($name),
                ':email' => InputValidator::sanitizeEmail($email),
                ':password' => $password,
                ':role' => $role,
                ':studyProgram' => $studyProgram,
                ':studyYear' => ($studyYear === "" || $studyYear === null) ? null : (int)$studyYear,
                ':imagePath' => $imagePath,
            ]);
        } catch (Exception $e) {
            Logger::error("Failed to create user: " . $e->getMessage());
            return false;
        }
    }

    // Retrieve a user by email
    public function getUserByEmail($email)
    {
        Logger::info("Getting user by email: " . $email);
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->pdo->prepare($sql);

        try {
            $stmt->execute([':email' => InputValidator::sanitizeEmail($email)]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            Logger::error("Failed to retrieve user by email: " . $e->getMessage());
            return null;
        }
    }

    // Retrieve a user by ID
    public function getUserById($id)
    {
        Logger::info("Getting user by id: " . $id);
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        try {
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            Logger::error("Failed to retrieve user by ID: " . $e->getMessage());
            return null;
        }
    }

    // Update a user's information
    public function updateUser($id, $name, $email, $password = null, $role = null, $studyProgram = null, $studyYear = null, $imagePath = null)
    {
        Logger::info("Updating user with id: " . $id);
        $sql = "UPDATE users SET 
                name = :name, 
                email = :email, 
                role = :role, 
                study_program = :studyProgram, 
                study_year = :studyYear, 
                image_path = :imagePath,
                updated_at = NOW()";

        if ($password) {
            $sql .= ", password = :password";
        }

        $sql .= " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        $params = [
            ':id' => $id,
            ':name' => InputValidator::sanitizeString($name),
            ':email' => InputValidator::sanitizeEmail($email),
            ':role' => $role,
            ':studyProgram' => $studyProgram,
            ':studyYear' => $studyYear,
            ':imagePath' => $imagePath,
        ];

        if ($password) {
            $params[':password'] = AuthHelper::hashPassword($password);
        }

        try {
            return $stmt->execute($params);
        } catch (Exception $e) {
            Logger::error("Failed to update user: " . $e->getMessage());
            return false;
        }
    }

    // Delete a user by ID
    public function deleteUser($id)
    {
        Logger::info("Deleting user with id: " . $id);
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        try {
            return $stmt->execute([':id' => $id]);
        } catch (Exception $e) {
            Logger::error("Failed to delete user: " . $e->getMessage());
            return false;
        }
    }

    // Get all users (optionally filter by role)
    public function getAllUsers($role = null)
    {
        Logger::info("Getting all users: " . $role);
        try {
            if ($role) {
                $sql = "SELECT * FROM users WHERE role = :role";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([':role' => $role]);
            } else {
                $sql = "SELECT * FROM users";
                $stmt = $this->pdo->query($sql);
            }

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            Logger::error("Failed to retrieve all users: " . $e->getMessage());
            return [];
        }
    }
    public function savePasswordResetToken($userId, $token) {
        try {
            $sql = "UPDATE users 
                    SET reset_token = :token,
                        reset_token_created_at = CURRENT_TIMESTAMP
                    WHERE id = :id";
                    
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':token' => $token,
                ':id' => $userId
            ]);
        } catch (Exception $e) {
            Logger::error("Failed to save reset token: " . $e->getMessage());
            return false;
        }
    }

    public function getUserByResetToken($token) {
        try {
            // Sjekk om token eksisterer og ikke er eldre enn 1 time
            $sql = "SELECT * FROM users 
                    WHERE reset_token = :token 
                    AND reset_token_created_at >= NOW() - INTERVAL 1 HOUR";
                    
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':token' => $token]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            Logger::error("Failed to get user by reset token: " . $e->getMessage());
            return null;
        }
    }

    public function updatePasswordAndClearToken($userId, $hashedPassword) {
        try {
            $sql = "UPDATE users 
                    SET password = :password,
                        reset_token = NULL,
                        reset_token_created_at = NULL
                    WHERE id = :id";
                    
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':password' => $hashedPassword,
                ':id' => $userId
            ]);
        } catch (Exception $e) {
            Logger::error("Failed to update password and clear token: " . $e->getMessage());
            return false;
        }
    }

    public function updatePassword($userId, $newHashedPassword)
    {
        $sql = "UPDATE users SET password = :hashedPassword, reset_token = NULL, reset_token_created_at = NULL WHERE id = :userId";
        $stmt = $this->pdo->prepare($sql);
    
        try {
            return $stmt->execute([
                ':hashedPassword' => $hashedPassword,  // Bruk det allerede hashede passordet
                ':userId' => $userId,
            ]);
            
            Logger::info("Password update result: " . ($result ? "success" : "failed"));
            return $result;
        } catch (Exception $e) {
            Logger::error("Failed to update password: " . $e->getMessage());
            return false;
        }
    }
}