<?php

namespace api\controllers;

use models\user\User;
use models\user\Student;
use models\user\Lecturer;
use db\DB;
use Exception;

require_once __DIR__ . '/../../models/user/User.php';
require_once __DIR__ . '/../../models/user/Student.php';
require_once __DIR__ . '/../../models/user/Lecturer.php';
require_once __DIR__ . '/../../database/DB.php';

class UserController {
    public function saveUser() {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                throw new Exception("Invalid input");
            }

            // Validate required fields
            $requiredFields = ['first_name', 'last_name', 'email', 'password', 'user_type'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Missing required field: $field");
                }
            }

            // Create User object
            $user = new User(
                0,
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['password'],
                $data['user_type'],
                null
            );

            // Save User to DB and get userId
            $userId = $user->saveToDB();

            // Create Student or Lecturer object based on user_type
            if ($data['user_type'] === 'student') {
                $requiredStudentFields = ['study_program', 'cohort_year'];
                foreach ($requiredStudentFields as $field) {
                    if (empty($data[$field])) {
                        throw new Exception("Missing required field: $field");
                    }
                }

                $student = new Student(
                    $userId,
                    $data['first_name'],
                    $data['last_name'],
                    $data['email'],
                    $data['password'],
                    $data['user_type'],
                    $data['study_program'],
                    $data['cohort_year'],
                    $user->getCreatedAt()
                );
                $student->saveToDB();
            } elseif ($data['user_type'] === 'lecturer') {
                $lecturer = new Lecturer(
                    $userId,
                    $data['first_name'],
                    $data['last_name'],
                    $data['email'],
                    $data['password'],
                    $data['user_type'],
                    $data['profile_image_path'] ?? null,
                    $user->getCreatedAt()
                );
                $lecturer->saveToDB();
            }

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

            $stmt = $conn->prepare("SELECT u.user_id, u.email, u.user_type, u.created_at, s.first_name, s.last_name, s.study_program, s.cohort_year, l.first_name AS lecturer_first_name, l.last_name AS lecturer_last_name, l.profile_image_path
                                    FROM users u
                                    LEFT JOIN students s ON u.user_id = s.student_id
                                    LEFT JOIN lecturers l ON u.user_id = l.lecturer_id
            ");
            $stmt->execute();
            $result = $stmt->get_result();
            $users = $result->fetch_all(MYSQLI_ASSOC);

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
?>