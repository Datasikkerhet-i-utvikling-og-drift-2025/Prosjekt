<?php

require_once __DIR__ . '/../helpers/InputValidator.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';
require_once __DIR__ . '/../helpers/Logger.php';

class User
{
    protected $pdo; // PDO instance

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Create a new user
    public function createUser($name, $email, $password, $role, $studyProgram = null, $studyYear = null, $imagePath = null)
    {

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