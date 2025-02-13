<?php

namespace services;

use helpers\Logger;
use models\User;
use service\DatabaseService;

class UserRepository
{

    private DatabaseService $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function createUser(User $user): bool
    {
        $sql = "INSERT INTO users (first_name, last_name, full_name, email, password, role, study_program, enrollment_year, image_path, created_at, updated_at) 
                VALUES (:first_name, :last_name, :full_name, :email, :password, :role, :studyProgram, :enrollmentYear, :imagePath, NOW(), NOW())";

        $stmt = $this->db->prepareSql($sql, [$user, 'bindUserDataForDbStmt']);
        $loggerMessage = "Save user data in database";
        return $this->db->executeSql($stmt, $loggerMessage);
    }

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

    public function getUserByEmail(User $user): bool
    {
        $sql = "SELECT * FROM users 
                WHERE email = :email";

        $stmt = $this->db->prepareSql($sql, [$user, 'bindUserDataForDbStmt']);
        $logger = "Getting user by email: " . $user->email;
        return $this->db->executeSql($stmt, $logger);
    }

    public function getUserById(User $user): bool
    {
        $sql = "SELECT * FROM users 
                WHERE id = :id";

        $stmt = $this->db->prepareSql($sql, [$user, 'bindUserDataForDbStmt']);
        $logger = "Getting user by id: " . $user->id;
        return $this->db->executeSql($stmt, $logger);
    }

    public function deleteUserById($user): bool
    {
        $sql = "DELETE FROM users 
                WHERE id = :id";

        $stmt = $this->db->prepareSql($sql, [$user, 'bindUserDataForDbStmt']);
        $logger = "Deleting user with id: " . $user->id;
        return $this->db->executeSql($stmt, $logger);
    }

}