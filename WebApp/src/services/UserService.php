<?php

namespace services;

use helpers\Database;
use helpers\Logger;
use models\User;

use JsonException;

class UserService
{

    private Database $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function createUser(User $user): bool
    {
        $sql = "INSERT INTO users 
                (first_name, last_name, full_name, email, password, role, study_program, enrollment_year, image_path, created_at, updated_at) 
                VALUES 
                (:first_name, :last_name, :full_name, :email, :password, :role, :studyProgram, :enrollmentYear, :imagePath, NOW(), NOW())";

        $stmt = $this->db->pdo->prepare($sql);
        $user->bindUserDataForDbStmt($stmt);

        $logger = "Save user data in database";

        return $this->db->executeStatement($stmt, $logger);
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

        $stmt = $this->db->pdo->prepare($sql);
        $user->bindUserDataForDbStmt($stmt);

        $logger = "Update user data in database";

        return $this->db->executeStatement($stmt, $logger);
    }


}