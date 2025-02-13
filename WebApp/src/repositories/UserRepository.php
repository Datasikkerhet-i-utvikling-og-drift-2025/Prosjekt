<?php

namespace repositories;

use factories\UserFactory;
use helpers\Logger;
use models\User;
use service\DatabaseService;

use DateMalformedStringException;

class UserRepository
{
    private DatabaseService $db;


    /**
     * Constructs a UserRepository instance.
     *
     * @param DatabaseService $db The database service instance for database interactions.
     */
    public function __construct(DatabaseService $db)
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
        Logger::debug("Checking the users data: " . json_encode((array) $user, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
        $sql = "INSERT INTO users (first_name, last_name, full_name, email, password, role, study_program, enrollment_year, image_path, created_at, updated_at) 
                VALUES (:first_name, :last_name, :full_name, :email, :password, :role, :studyProgram, :enrollmentYear, :imagePath, NOW(), NOW())";

        $stmt = $this->db->prepareSql($sql, [$user, 'bindUserDataForDbStmt']);
        $loggerMessage = "Save user data in database";

        return $this->db->executeSql($stmt, $loggerMessage);
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
                SET first_name = :first_name,
                    last_name = :last_name,
                    full_name = :full_name,
                    email = :email,
                    password = :password,
                    role = :role,
                    study_program = :studyProgram,
                    enrollment_year = :enrollmentYear,
                    image_path = :imagePath,
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepareSql($sql, [$user, 'bindUserDataForDbStmt']);
        $logger = "Update user data in database";

        return $this->db->executeSql($stmt, $logger);
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

        $stmt = $this->db->prepareSql($sql);
        $this->db->bindSingleValueToSqlStmt($stmt, ":id", $userId);
        $logger = "Deleting user with ID: " . $userId;

        return $this->db->executeSql($stmt, $logger);
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

        $stmt = $this->db->prepareSql($sql);
        $this->db->bindSingleValueToSqlStmt($stmt, ":email", $userEmail);
        $logger = "Deleting user with email: " . $userEmail;

        return $this->db->executeSql($stmt, $logger);
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
                WHERE id = :id LIMIT 1";

        $stmt = $this->db->prepareSql($sql);
        $this->db->bindSingleValueToSqlStmt($stmt, ":id", $userId);
        $logger = "Getting user by ID: " . $userId;

        $userData = $this->db->fetchSingle($stmt, $logger);

        if (!$userData) {
            return null;
        }

        return UserFactory::createUser($userData);
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
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";

        $stmt = $this->db->prepareSql($sql);
        $this->db->bindSingleValueToSqlStmt($stmt, ":email", $email);
        $logger = "Fetching user by email: " . $email;

        $userData = $this->db->fetchSingle($stmt, $logger);

        if (!$userData) {
            return null;
        }

        return UserFactory::createUser($userData);
    }



    /**
     * Retrieves all users from the database.
     *
     * @return User[] Returns an array of User objects.
     * @throws DateMalformedStringException
     */
    public function getAllUsers(): array
    {
        $sql = "SELECT * FROM users";

        $stmt = $this->db->prepareSql($sql);
        $logger = "Fetching all users from the database";

        $usersData = $this->db->fetchAll($stmt, $logger);

        $users = [];
        foreach ($usersData as $userData) {
            $users[] = UserFactory::createUser($userData);
        }

        return $users;
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

        $stmt = $this->db->prepareSql($sql);
        $this->db->bindArrayToSqlStmt($stmt, [':token', ':id'], [$token, $userId]);
        $logger = "Saving reset token for user ID: " . $userId;

        return $this->db->executeSql($stmt, $logger);
    }


    /**
     * Retrieves a user by their password reset token.
     *
     * @param string $token The reset token.
     * @return User|null Returns a User object if found, otherwise null.
     */
    public function getUserByResetToken(string $token): ?User
    {
        $sql = "SELECT * FROM users 
                WHERE reset_token = :token 
                AND reset_token_created_at >= NOW() - INTERVAL 1 HOUR";

        $stmt = $this->db->prepareSql($sql);
        $this->db->bindSingleValueToSqlStmt($stmt, ":token", $token);

        $logger = "Fetching user by reset token";
        $userData = $this->db->fetchSingle($stmt, $logger);

        if (!$userData) {
            return null;
        }

        return UserFactory::createUser($userData);
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

        $stmt = $this->db->prepareSql($sql);
        $this->db->bindArrayToSqlStmt($stmt, [':password', ':id'], [$hashedPassword, $userId]);

        $logger = "Updating password and clearing reset token for user ID: " . $userId;
        return $this->db->executeSql($stmt, $logger);
    }


    /**
     * Updates a user's password.
     *
     * @param string $userId The ID of the user.
     * @param string $hashedPassword The new hashed password.
     * @return bool Returns true if the update was successful, false otherwise.
     */
    public function updatePassword(string $userId, string $hashedPassword): bool
    {
        $sql = "UPDATE users 
                SET password = :hashedPassword
                WHERE id = :userId";

        $stmt = $this->db->prepareSql($sql);
        $this->db->bindArrayToSqlStmt($stmt, [':hashedPassword', ':userId'], [$hashedPassword, $userId]);

        $logger = "Updating password for user ID: " . $userId;
        return $this->db->executeSql($stmt, $logger);
    }
}
