<?php

namespace api\controllers;

use models\user\User;
use db\DB;
use Exception;

require_once __DIR__ . '/../../models/user/User.php';
require_once __DIR__ . '/../../database/DB.php';

class UserController {
    public function saveUser() {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                throw new Exception("Invalid input");
            }

            $user = new User(
                0,
                $data['email'],
                $data['password'],
                $data['created_at'] ?? null,
                $data['first_name'],
                $data['last_name'],
            );

            $userId = $user->saveToDB();

            echo json_encode([
                'success' => true,
                'message' => 'User created successfully',
                'userId' => $userId,
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function getAllUsers() {
        header('Content-Type: application/json');

        try {
            $db = new DB();
            $conn = $db->getConnection();

            $stmt = $conn->prepare("SELECT user_id, email, created_at, first_name, last_name FROM users");
            $stmt->execute();

            $users = $stmt->fetchAll();

            echo json_encode([
                'success' => true,
                'data' => $users,
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function loginUser()
    {
        $email = $_POST['email'];
        $password = $_POST['password'];

        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['error' => 'Email and password are required']);
            return;
        }

        $stmt = $this->dbConnection->prepare("SELECT user_id, email, password_hash, first_name, last_name, created_at FROM users WHERE email = ?");
        if (!$stmt) {
            http_response_code(500);
            echo json_encode(['error' => 'Database query preparation failed']);
            return;
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password_hash'])) {
            $userModel = new User(
                $user['user_id'],
                $user['email'],
                $password, // Store unhashed password temporarily
                $user['created_at'],
                $user['first_name'],
                $user['last_name']
            );

            http_response_code(200);
            echo json_encode(['message' => 'Login successful', 'user' => $userModel]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid email or password']);
        }

        $stmt->close();
        $this->dbConnection->close();
    }
}
