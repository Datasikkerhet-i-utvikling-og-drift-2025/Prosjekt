<?php

namespace api\controllers;

use db\DB;

require_once __DIR__ . '/../../database/DB.php';

class UserController
{
    private $dbConnection;

    public function __construct()
    {
        $database = new DB();
        $this->dbConnection = $database->getConnection();
    }

    public function getAllUsers()
    {
        $sql = "SELECT user_id, email, user_type, created_at FROM users";
        $result = $this->dbConnection->query($sql);

        $users = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }

        header('Content-Type: application/json');
        echo json_encode($users);

        $this->dbConnection->close();
    }

    public function createUser()
    {
        $jsonData = file_get_contents("php://input");
        $data = json_decode($jsonData, true);

        if (!isset($data['email'], $data['password'], $data['user_type'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing required fields"]);
            return;
        }

        $email = $data['email'];
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        $userType = $data['user_type'];

        $stmt = $this->dbConnection->prepare("INSERT INTO users (email, password_hash, user_type) VALUES (?, ?, ?)");
        if (!$stmt) {
            http_response_code(500);
            echo json_encode(["error" => "Database prepare failed"]);
            return;
        }

        $stmt->bind_param("sss", $email, $passwordHash, $userType);

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(["message" => "User created successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => $stmt->error]);
        }

        $stmt->close();
        $this->dbConnection->close();
    }
}
