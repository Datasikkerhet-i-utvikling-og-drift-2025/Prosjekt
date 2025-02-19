<?php

namespace repositories;

use DateMalformedStringException;
use factories\UserFactory;
use helpers\Logger;
use managers\DatabaseManager;
use models\User;
use PDO;

class UserRepository
{
    private DatabaseManager $db;


    /**
     * Constructs a UserRepository instance.
     *
     * @param DatabaseManager $db The database service instance for database interactions.
     */
    public function __construct(DatabaseManager $db)
    {
        $this->db = $db;
    }


    /**
     * Creates a new user in the database.
     *
     * @param User $user The user object containing user details.
     * @return bool Returns true if the user was created successfully, false otherwise.
     */
    public function createUser(User $user): bool
    {
        $sql = "INSERT INTO users (first_name, last_name, full_name, email, password, role, study_program, enrollment_year, image_path, created_at, updated_at) 
                VALUES (:firstName, :lastName, :fullName, :email, :password, :role, :studyProgram, :enrollmentYear, :imagePath, NOW(), NOW())";

        $this->db->prepareStmt(
            $sql,
            fn($stmt) => $user->bindUserDataForDbStmt($stmt)
        );

        return $this->db->executeStmt("Saving user data in database");
    }


    /**
     * Updates an existing user's details in the database.
     *
     * @param User $user The user object containing updated user details.
     * @return bool Returns true if the update was successful, false otherwise.
     */
    public function updateUser(User $user): bool
    {
        if ($user->id === null) {
            Logger::error("Failed to update user: ID is null");
            return false;
        }

        $sql = "UPDATE users 
                SET first_name = :firstName,
                    last_name = :lastName,
                    full_name = :fullName,
                    email = :email,
                    password = :password,
                    role = :role,
                    study_program = :studyProgram,
                    enrollment_year = :enrollmentYear,
                    image_path = :imagePath,
                    updated_at = NOW()
                WHERE id = :id";

        $this->db->prepareStmt(
            $sql,
            fn($stmt) => $user->bindUserDataForDbStmt($stmt)
        );

        return $this->db->executeStmt("Updating user data in database");
    }

    /**
     * Deletes a user from the database by their ID.
     *
     * @param string $userId The ID of the user to be deleted.
     * @return bool Returns true if the deletion was successful, false otherwise.
     */
    public function deleteUserById(string $userId): bool
    {
        $sql = "DELETE FROM users 
                WHERE id = :id";

        $this->db->prepareStmt(
            $sql,
            fn($stmt) => $stmt->bindValue(":id", $userId, PDO::PARAM_STR)
        );

        return $this->db->executeStmt("Deleting user with ID: $userId");
    }


    /**
     * Deletes a user from the database by their email.
     *
     * @param string $userEmail The email of the user to be deleted.
     * @return bool Returns true if the deletion was successful, false otherwise.
     */
    public function deleteUserByEmail(string $userEmail): bool
    {
        $sql = "DELETE FROM users 
                WHERE email = :email";

        $this->db->prepareStmt(
            $sql,
            fn($stmt) => $stmt->bindValue(":email", $userEmail, PDO::PARAM_STR)
        );

        return $this->db->executeStmt("Deleting user with email: $userEmail");
    }


    /**
     * Retrieves a user from the database by their ID.
     *
     * @param string $userId The ID of the user to retrieve.
     * @return User|null Returns a User object if found, otherwise null.
     * @throws DateMalformedStringException
     */
    public function getUserById(string $userId): ?User
    {
        $sql = "SELECT * FROM users 
                WHERE id = :id 
                LIMIT 1";

        $this->db->prepareStmt(
            $sql,
            fn($stmt) => $stmt->bindValue(":id", $userId, PDO::PARAM_STR)
        );
        $userData = $this->db->fetchSingle("Fetching user by ID: $userId");

        return $userData ? UserFactory::createUser($userData) : null;
    }


    /**
     * Retrieves a user from the database by their email address.
     *
     * @param string $email The email address of the user to retrieve.
     * @return User|null Returns a User object if found, otherwise null.
     * @throws DateMalformedStringException
     */
    public function getUserByEmail(string $email): ?User
    {
        $sql = "SELECT * FROM users 
                WHERE email = :email 
                LIMIT 1";

        $this->db->prepareStmt($sql, fn($stmt) => $stmt->bindValue(":email", $email, PDO::PARAM_STR));
        $userData = $this->db->fetchSingle("Fetching user by email: $email");

        return $userData ? UserFactory::createUser($userData) : null;
    }


    /**
     * Retrieves all users from the database.
     *
     * @return User[] Returns an array of User objects.
     */
    public function getAllUsers(): array
    {
        $sql = "SELECT * 
                FROM users";

        $this->db->prepareStmt($sql);
        $usersData = $this->db->fetchAll("Fetching all users from the database");

        return array_map([UserFactory::class, 'createUser'], $usersData);
    }


    /**
     * Stores a password reset token for a user.
     *
     * @param string $userId The ID of the user.
     * @param string $token The generated reset token.
     * @return bool Returns true if the update was successful, false otherwise.
     */
    public function savePasswordResetToken(string $userId, string $token): bool
    {
        $sql = "UPDATE users 
                SET reset_token = :token, 
                    reset_token_created_at = NOW() 
                WHERE id = :id";

        $this->db->prepareStmt($sql,
            fn($stmt) => $stmt
                ->bindValue(":token", $token, PDO::PARAM_STR)
                ->bindValue(":id", $userId, PDO::PARAM_STR)
        );

        return $this->db->executeStmt("Saving reset token for user ID: $userId");
    }


    /**
     * Retrieves a user by their password reset token.
     *
     * @param string $token The reset token.
     * @return User|null Returns a User object if found, otherwise null.
     * @throws DateMalformedStringException
     */
    public function getUserByResetToken(string $token): ?User
    {
        $sql = "SELECT * 
                FROM users 
                WHERE reset_token = :token 
                  AND reset_token_created_at >= NOW() - INTERVAL 1 HOUR";

        $this->db->prepareStmt(
            $sql,
            fn($stmt) => $stmt->bindValue(":token", $token, PDO::PARAM_STR)
        );
        $userData = $this->db->fetchSingle("Fetching user by reset token");

        return $userData ? UserFactory::createUser($userData) : null;
    }


    /**
     * Updates a user's password and clears the reset token.
     *
     * @param string $userId The ID of the user.
     * @param string $hashedPassword The new hashed password.
     * @return bool Returns true if the update was successful, false otherwise.
     */
    public function updatePasswordAndClearToken(string $userId, string $hashedPassword): bool
    {
        $sql = "UPDATE users 
                SET password = :password, 
                    reset_token = NULL, 
                    reset_token_created_at = NULL 
                WHERE id = :id";

        $this->db->prepareStmt(
            $sql,
            fn($stmt) => $stmt
                ->bindValue(":password", $hashedPassword, PDO::PARAM_STR)
                ->bindValue(":id", $userId, PDO::PARAM_STR)
        );

        return $this->db->executeStmt("Updating password and clearing reset token for user ID: $userId");
    }



    // FIXME look at the implementation here
    // Save a password reset token
    public function v1savePasswordResetToken($userId, $resetToken)
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
    public function v1getUserByResetToken($resetToken)
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
}
