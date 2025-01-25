<?php

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
        $sql = "INSERT INTO users (name, email, password, role, study_program, study_year, image_path)
                VALUES (:name, :email, :password, :role, :studyProgram, :studyYear, :imagePath)";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':role' => $role,
            ':studyProgram' => $studyProgram,
            ':studyYear' => $studyYear,
            ':imagePath' => $imagePath,
        ]);
    }

    // Retrieve a user by their email
    public function getUserByEmail($email)
    {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => $email]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Retrieve a user by their ID
    public function getUserById($id)
    {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
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
                image_path = :imagePath";

        // Only update password if provided
        if ($password) {
            $sql .= ", password = :password";
        }

        $sql .= " WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);

        $params = [
            ':id' => $id,
            ':name' => $name,
            ':email' => $email,
            ':role' => $role,
            ':studyProgram' => $studyProgram,
            ':studyYear' => $studyYear,
            ':imagePath' => $imagePath,
        ];

        if ($password) {
            $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        return $stmt->execute($params);
    }

    // Delete a user
    public function deleteUser($id)
    {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([':id' => $id]);
    }

    // Get all users (optional filtering by role)
    public function getAllUsers($role = null)
    {
        if ($role) {
            $sql = "SELECT * FROM users WHERE role = :role";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':role' => $role]);
        } else {
            $sql = "SELECT * FROM users";
            $stmt = $this->pdo->query($sql);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
