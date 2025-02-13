<?php

namespace controllers;

require_once __DIR__ . '/../helpers/ApiHelper.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';
require_once __DIR__ . '/../helpers/InputValidator.php';
require_once __DIR__ . '/../helpers/Logger.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/Mailer.php';
require_once __DIR__ . '/../models/Course.php';


class AuthController
{
    private $userModel;
    private $pdo;

    public function __construct($pdo)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->pdo = $pdo;
        $this->userModel = new User($pdo);
    }
    // User Registration
    public function register()
    {
        // Validate the form submission
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); // Method Not Allowed
            echo "Invalid request method.";
            return;
        }

        $input = $_POST;

        // Combine first_name and last_name into a single name field
        $input['name'] = trim(($input['first_name'] ?? '') . ' ' . ($input['last_name'] ?? ''));

        // Validate input using the InputValidator
        $validation = InputValidator::validateRegistration($input);

        // Check for validation errors
        $validation = $this->checkForError($validation);
        
        // Hash password
        $hashedPassword = AuthHelper::hashPassword($validation['sanitized']['password']);

        // File upload validation if role is 'lecturer'
        list($profilePicturePath, $validation) = $this->profilePictureUpload($validation);

        // Check for existing email
        if (empty($validation['errors']) && $this->userModel->getUserByEmail($validation['sanitized']['email'])) {
            $validation['errors'][] = "Email is already registered.";
        }

        // If errors exist, return to the registration form with error messages
        $validation = $this->checkForError($validation);

        // Create user in the database
        $this->createUserInTheDatabase($validation['sanitized'], $hashedPassword, $profilePicturePath);

        ApiHelper::sendResponse(200, [
            'redirect' => '/',
            'message' => 'Registration successful'
        ]);
    }

    // User Login
    public function login()
    {
        $input = ApiHelper::getJsonInput();

        // Validate input
        ApiHelper::validateRequest(['email', 'password'], $input);

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // Enkel sjekk for at e-post og passord er sendt med
        if (empty($email) || empty($password)) {
            header("Location: /login?error=" . urlencode("Email and password are required."));
            exit;
        }

        // Find user by email
        $user = $this->userModel->getUserByEmail($input['email']);
        if (!$user || !AuthHelper::verifyPassword($input['password'], $user['password'])) {
            Logger::error("Login failed for email: " . $input['email']);
            //ApiHelper::sendError(401, 'Invalid email or password.');
            header("Location: /?error=" . urlencode("Invalid email or password."));
            exit;
        }

        // Log in the user by setting session or token
        AuthHelper::loginUser($user);
        Logger::info("User logged in: " . $input['email'] . ". Session data: " . var_export($_SESSION, true));

        // Redirect to the user page based on role
        if ($user['role'] === 'student') {
            header('Location: /student/dashboard');
            exit;
        } elseif ($user['role'] === 'lecturer') {
            header('Location: /lecturer/dashboard');
            exit;
        } elseif ($user['role'] === 'admin') {
            header('Location: /admin/dashboard');
            exit;
        } else {
            Logger::error("Unknown role for user ID: " . $user['id']);
            ApiHelper::sendError(400, 'Unknown role for user.');
        }
    }

    // User Logout
    public function logout()
    {
        AuthHelper::logoutUser();
        Logger::info("User logged out.");
        header('Location: /');
    }

    public function getUserById($userId)
    {
        return $this->userModel->getUserById($userId);
    }
    
    public function changePassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['errors'] = 'Invalid request method';
            header('Location: /profile');
            exit;
        }
    
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $userId = $_SESSION['user']['id'] ?? null;
    
        // Validate input
        if (!$userId) {
            $_SESSION['errors'] = 'User not logged in';
            header('Location: /profile');
            exit;
        }
    
        if (strlen($newPassword) < 8) {
            $_SESSION['errors'] = 'New password must be at least 8 characters long';
            header('Location: /profile');
            exit;
        }
    
        if ($newPassword !== $confirmPassword) {
            $_SESSION['errors'] = 'New passwords do not match';
            header('Location: /profile');
            exit;
        }
    
        // Verify current password
        $user = $this->userModel->getUserById($userId);
        if (!$user || !AuthHelper::verifyPassword($currentPassword, $user['password'])) {
            $_SESSION['errors'] = 'Current password is incorrect';
            header('Location: /profile');
            exit;
        }
    
        // Update password
        $hashedPassword = AuthHelper::hashPassword($newPassword);
        if ($this->userModel->updatePassword($userId, $hashedPassword)) {
            $_SESSION['success'] = 'Password updated successfully';
        } else {
            $_SESSION['errors'] = 'Failed to update password';
        }
    
        header('Location: /profile');
        exit;
    }

    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $userId = $_SESSION['user']['id'] ?? null;

    Logger::info("Attempting to change password for user ID: " . $userId);

    // Valider input
    if (!$userId) {
        $_SESSION['errors'] = 'User not logged in';
        header('Location: /profile');
        exit;
    }

    if (strlen($newPassword) < 8) {
        $_SESSION['errors'] = 'New password must be at least 8 characters long';
        header('Location: /profile');
        exit;
    }

    if ($newPassword !== $confirmPassword) {
        $_SESSION['errors'] = 'New passwords do not match';
        header('Location: /profile');
        exit;
    }

    // Verifiser nåværende passord
    $user = $this->userModel->getUserById($userId);
    if (!$user || !AuthHelper::verifyPassword($currentPassword, $user['password'])) {
        $_SESSION['errors'] = 'Current password is incorrect';
        header('Location: /profile');
        exit;
    }

    // Oppdater passord
    $hashedPassword = AuthHelper::hashPassword($newPassword);
    if ($this->userModel->updatePassword($userId, $hashedPassword)) {
        $_SESSION['success'] = 'Password updated successfully';
    } else {
        $_SESSION['errors'] = 'Failed to update password';
    }

    header('Location: /profile');
    exit;
}

public function requestPasswordReset() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /forgot-password');
        exit;
    }

    try {
        $email = $_POST['email'] ?? '';
        
        // Finn bruker
        $user = $this->userModel->getUserByEmail($email);
        if (!$user) {
            throw new Exception('If this email exists in our system, you will receive a reset link.');
        }

        // Generer token
        $resetToken = bin2hex(random_bytes(32));
        $tokenExpiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Lagre token i databasen
        if (!$this->userModel->savePasswordResetToken($user['id'], $resetToken, $tokenExpiry)) {
            throw new Exception('Failed to process reset request');
        }

        // Send email
        $mailer = new Mailer();
        if (!$mailer->sendPasswordReset($email, $resetToken)) {
            throw new Exception('Failed to send reset email');
        }

        $_SESSION['success'] = 'If this email exists in our system, you will receive a reset link.';
        header('Location: /login');
        exit;

    } catch (Exception $e) {
        $_SESSION['errors'] = $e->getMessage();
        header('Location: /forgot-password');
        exit;
    }
}

public function resetPassword()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /reset-password');
        exit;
    }

    $token = $_POST['token'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($newPassword !== $confirmPassword) {
        header('Location: /reset-password?token=' . urlencode($token) . '&error=' . urlencode('Passwords do not match'));
        exit;
    }

    // Verify token and get user
    $user = $this->userModel->getUserByResetToken($token);
    if (!$user) {
        header('Location: /reset-password?error=' . urlencode('Invalid or expired reset token'));
        exit;
    }

    // Update password
    $hashedPassword = AuthHelper::hashPassword($newPassword);
    if ($this->userModel->updatePassword($user['id'], $hashedPassword)) {
        header('Location: /login?success=' . urlencode('Password has been reset successfully'));
    } else {
        header('Location: /reset-password?token=' . urlencode($token) . '&error=' . urlencode('Failed to reset password'));
    }
    exit;
}

    public function createUserInTheDatabase($sanitized, string $hashedPassword, ?string $profilePicturePath): void
    {
        try {
            $this->pdo->beginTransaction();

            // Opprett bruker
            $userCreated = $this->userModel->createUser(
                $sanitized['name'],
                $sanitized['email'],
                $hashedPassword,
                $sanitized['role'],
                $sanitized['study_program'] ?? null,
                $sanitized['cohort_year'] ?? null,
                $profilePicturePath
            );

            if (!$userCreated) {
                throw new Exception("Failed to create user");
            }

            // Hvis det er en foreleser og kursinformasjon er gitt, opprett kurs
            if ($sanitized['role'] === 'lecturer' && 
                isset($sanitized['course_code']) && 
                isset($sanitized['course_name']) && 
                isset($sanitized['course_pin'])) {
                
                $courseModel = new Course($this->pdo);
                $userId = $this->pdo->lastInsertId();
                
                $courseCreated = $courseModel->createCourse(
                    $sanitized['course_code'],
                    $sanitized['course_name'],
                    $userId,
                    $sanitized['course_pin']
                );

                if (!$courseCreated) {
                    throw new Exception("Failed to create course");
                }
            }

            $this->pdo->commit();
            $_SESSION['success'] = "Registration successful. Please log in.";
            header("Location: /");
            exit;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            $_SESSION['errors'] = [$e->getMessage()];
            header("Location: /register");
            exit;
        }
    }



    /**
     * @param array $validation
     * @return array
     */
    public function profilePictureUpload(array $validation): array
    {
        $profilePicturePath = null;
        if ($validation['sanitized']['role'] === 'lecturer' && isset($_FILES['profile_picture'])) {
            $file = $_FILES['profile_picture'];
            $uploadDir = __DIR__ . '/../../public/uploads/profile_pictures/';
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $uploadDir));
            }

            // Check file type and size
            if ($file['error'] === UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg', 'image/png'];
                if (!in_array($file['type'], $allowedTypes)) {
                    $validation['errors'][] = "Invalid file type for profile picture. Only JPG and PNG are allowed.";
                    Logger::error("Invalid file type for profile picture: " . $file['type']);
                } elseif ($file['size'] > 2 * 1024 * 1024) { // 2MB limit
                    $validation['errors'][] = "Profile picture size must not exceed 2MB.";
                    Logger::error("Profile picture size exceeds limit: " . $file['size'] . " bytes");
                } else {
                    $profilePicturePath = $uploadDir . basename($file['name']);
                    move_uploaded_file($file['tmp_name'], $profilePicturePath);
                }
            } else {
                $validation['errors'][] = "Profile picture is required for lecturers.";
                Logger::error("Profile picture upload error: " . var_export($file, true));
            }
            return array('/uploads/profile_pictures/'.$file['name'], $validation);
        }
        else {
            return array($profilePicturePath, $validation);
        }
    }

    /**
     * @param array $validation
     * @return array|void
     */
    public function checkForError(array $validation)
    {
        if (!empty($validation['errors'])) {
            $_SESSION['errors'] = $validation['errors'];
            header("Location: /register");
            exit;
        }
        return $validation;
    }

    public function guest()
    {
        // Set session data for guest
        $_SESSION['user'] = [
            'role' => 'guest',
            'name' => 'Guest'
        ];

        Logger::info("Guest user logged in. Session data: " . var_export($_SESSION, true));

        // Redirect to guest dashboard
        header('Location: /guests/dashboard');
        exit;
    }
}
