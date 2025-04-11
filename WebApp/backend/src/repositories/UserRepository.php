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
        $sql = "CALL createUser(:firstName, :lastName, :fullName, :email, :password, :role, :studyProgram, :enrollmentYear, :imagePath)";

        $stmt = $this->db->prepareStmt(
            $sql,
            fn($stmt) => $user->bindUserDataForDbStmt($stmt)
        );

        return $this->db->executeTransaction("Saving user data in database");
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
        $sql = "CALL getUserByEmail(:email)";

        $this->db->prepareStmt($sql, fn($stmt) => $stmt->bindValue(":email", $email, PDO::PARAM_STR));
        $userData = $this->db->fetchSingle("Fetching user by email: $email");

        return $userData ? UserFactory::createUser($userData) : null;
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

        $sql = "CALL updateUser(:id, :firstName, :lastName, :fullName, :email, :password, :role, :studyProgram, :enrollmentYear, :imagePath)";

        $this->db->prepareStmt(
            $sql,
            fn($stmt) => $user->bindUserDataForDbStmt($stmt)
        );

        return $this->db->executeTransaction("Updating user data in database");
    }


    /**
     * Deletes a user from the database by their ID.
     *
     * @param string $userId The ID of the user to be deleted.
     * @return bool Returns true if the deletion was successful, false otherwise.
     */
    public function deleteUserById(string $userId): bool
    {
        $sql = "CALL deleteUserById(:id)";

        $this->db->prepareStmt(
            $sql,
            fn($stmt) => $stmt->bindValue(":id", $userId, PDO::PARAM_STR)
        );

        return $this->db->executeTransaction("Deleting user with ID: $userId");
    }


    /**
     * Deletes a user from the database by their email.
     *
     * @param string $userEmail The email of the user to be deleted.
     * @return bool Returns true if the deletion was successful, false otherwise.
     */
    public function deleteUserByEmail(string $userEmail): bool
    {
        $sql = "CALL deleteUserByEmail(:email)";

        $this->db->prepareStmt(
            $sql,
            fn($stmt) => $stmt->bindValue(":email", $userEmail, PDO::PARAM_STR)
        );

        return $this->db->executeTransaction("Deleting user with email: $userEmail");
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
        $sql = "CALL getUserById(:id)";

        $this->db->prepareStmt(
            $sql,
            fn($stmt) => $stmt->bindValue(":id", $userId, PDO::PARAM_STR)
        );
        $userData = $this->db->fetchSingle("Fetching user by ID: $userId");

        return $userData ? UserFactory::createUser($userData) : null;
    }


    /**
     * Retrieves all users from the database.
     *
     * @return User[] Returns an array of User objects.
     */
    public function getAllUsers(): array
    {
        $sql = "CALL getAllUsers()";

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
        $sql = "CALL savePasswordResetToken(:id, :token)";

        $this->db->prepareStmt($sql,
            fn($stmt) => $stmt
                ->bindValue(":token", $token, PDO::PARAM_STR)
                ->bindValue(":id", $userId, PDO::PARAM_STR)
        );

        return $this->db->executeTransaction("Saving reset token for user ID: $userId");
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
        $sql = "CALL getUserByResetToken(:token)";

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
        $sql = "CALL updatePasswordAndClearToken(:id, :password)";

        $this->db->prepareStmt(
            $sql,
            fn($stmt) => $stmt
                ->bindValue(":password", $hashedPassword, PDO::PARAM_STR)
                ->bindValue(":id", $userId, PDO::PARAM_STR)
        );

        return $this->db->executeTransaction("Updating password and clearing reset token for user ID: $userId");
    }

    public function updatePassword(int $userId, string $hashedPassword): bool
    {
        $sql = "UPDATE users SET password = :password WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':password', $hashedPassword, PDO::PARAM_STR);
            $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
            return $stmt->execute(); // Returnerer true hvis spørringen kjørte ok
        } catch (PDOException $e) {
            error_log("Database Error (updatePassword): " . $e->getMessage());
            return false;
        }
    }
}
