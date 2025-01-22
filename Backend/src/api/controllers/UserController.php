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
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['password'],
                $data['user_type'],
                null
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

            $stmt = $conn->prepare("SELECT user_id, email, user_type, created_at FROM users");
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
}
