<?php

require_once __DIR__ . '/../helpers/ApiHelper.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';
require_once __DIR__ . '/../helpers/InputValidator.php';
require_once __DIR__ . '/../helpers/Logger.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/Mailer.php';

use helpers\ApiHelper;
use helpers\AuthHelper;

class AuthController
{
    private $userModel;

    public function __construct($pdo)
    {
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
    }

    // User Login
    public function login()
    {

        // Validate the form submission
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405); // Method Not Allowed
        echo "Invalid request method.";
        return;
    }

    $input = $_POST;

    // Validate input using the InputValidator
    $validation = InputValidator::validateLogin($input);

    // Check for validation errors
    if (!empty($validation['errors'])) {
        $_SESSION['errors'] = $validation['errors'];
        header("Location: /auth/login");
        exit;
    }

    // Sanitize input
    $sanitized = $validation['sanitized'];

    // Check user credentials
    $user = $this->userModel->getUserByEmail($sanitized['email']);
    if ($user && AuthHelper::verifyPassword($sanitized['password'], $user['password'])) {
        // Set user session or token
        $_SESSION['user'] = $user;
        header("Location: /dashboard");
        exit;
    } else {
        $_SESSION['errors'] = ['Invalid email or password.'];
        header("Location: /auth/login");
        exit;
    }

        // Validate the form submission
        // if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        //     http_response_code(405); // Method Not Allowed
        //     echo "Invalid request method.";
        //     return;
        // }

        // $input = $_POST;

        // if (empty($input['email']) || empty($input['password'])) {
        //     $_SESSION['errors'] = ['Email and password are required.'];
        //     header("Location: /login");
        //     exit;
        // }
        // if ($response !== false) {
        //     $responseData = json_decode($response, true);
        //     if ($responseData['success'] ?? false) {
        //         // Redirect or handle success
        //     } else {
        //         $error = $responseData['error'] ?? 'An unknown error occurred.';
        //     }
        // } else {
        //     $error = "Error connecting to the backend. " . error_get_last()['message'];
        // }

        // // Sanitize input
        // $email = InputValidator::sanitizeEmail($input['email']);
        // $password = InputValidator::sanitizeString($input['password']);

        // // Validate input using the InputValidator
        // ApiHelper::validateRequest(['email', 'password'], ['email' => $email, 'password' => $password]);

        // // Check for existing email
        // $user = $this->userModel->getUserByEmail($email);
        // if (!$user) {
        //     Logger::error("Login failed: Email not found.");
        //     header('Location: /auth/login?error=Invalid email or password.');
        //     exit;
        // }

        // // Verify password
        // if (!AuthHelper::verifyPassword($password, $user['password'])) {
        //     Logger::error("Login failed: Incorrect password for email " . $email);
        //     header('Location: /auth/login?error=Invalid email or password.');
        //     exit;
        // }

        // // Log in the user
        // AuthHelper::loginUser($user['id'], $user['role']);
        // Logger::info("User logged in successfully: " . $email);

        // // Redirect based on role
        // if ($user['role'] === 'student') {
        //     Logger::info("Redirecting to /student/dashboard");
        //     header('Location: /student/dashboard');
        // } elseif ($user['role'] === 'lecturer') {
        //     Logger::info("Redirecting to /lecturer/dashboard");
        //     header('Location: /lecturer/dashboard');
        // } else {
        //     Logger::error("Unauthorized role: " . $user['role']);
        //     header('Location: /auth/login?error=Unauthorized role.');
        // }
        // exit;
    }

    // User Logout
    public function logout()
    {
        AuthHelper::logoutUser();
        Logger::info("User logged out.");
        ApiHelper::sendResponse(200, [], 'User logged out successfully.');
    }

    // Password Reset Request
    public function requestPasswordReset()
    {
        $input = ApiHelper::getJsonInput();
        ApiHelper::validateRequest(['email'], $input);

        $user = $this->userModel->getUserByEmail($input['email']);
        if (!$user) {
            Logger::error("Password reset request failed: Email not found.");
            ApiHelper::sendError(404, 'Email not found.');
        }

        // Generate reset token and link
        $resetToken = bin2hex(random_bytes(16));
        $resetLink = getenv('APP_URL') . "/reset-password?token=$resetToken"; // Use environment variable for base URL

        // Save the reset token
        if ($this->userModel->savePasswordResetToken($user['id'], $resetToken)) {
            // Send password reset email
            $mailer = new Mailer();
            if ($mailer->sendPasswordReset($input['email'], $resetLink)) {
                Logger::info("Password reset email sent to: " . $input['email']);
                ApiHelper::sendResponse(200, [], 'Password reset email sent successfully.');
            } else {
                Logger::error("Failed to send password reset email to: " . $input['email']);
                ApiHelper::sendError(500, 'Failed to send password reset email.');
            }
        } else {
            Logger::error("Failed to save password reset token for email: " . $input['email']);
            ApiHelper::sendError(500, 'Failed to process password reset request.');
        }
    }

    // Password Reset
    public function resetPassword()
    {
        $input = ApiHelper::getJsonInput();
        ApiHelper::validateRequest(['token', 'new_password'], $input);

        $user = $this->userModel->getUserByResetToken($input['token']);
        if (!$user) {
            Logger::error("Password reset failed: Invalid or expired token.");
            ApiHelper::sendError(400, 'Invalid or expired token.');
        }

        // Update password
        $hashedPassword = AuthHelper::hashPassword($input['new_password']);
        if ($this->userModel->updatePassword($user['id'], $hashedPassword)) {
            Logger::info("Password reset successfully for user ID: " . $user['id']);
            ApiHelper::sendResponse(200, [], 'Password reset successfully.');
        } else {
            Logger::error("Failed to reset password for user ID: " . $user['id']);
            ApiHelper::sendError(500, 'Failed to reset password.');
        }
    }

    public function createUserInTheDatabase($sanitized, string $hashedPassword, ?string $profilePicturePath): void
    {
        if ($this->userModel->createUser(
            $sanitized['name'],
            $sanitized['email'],
            $hashedPassword,
            $sanitized['role'],
            $sanitized['study_program'] ?? null,
            $sanitized['cohort_year'] ?? null,
            $profilePicturePath // Save file path if lecturer has uploaded a picture
        )) {
            // Redirect to login page
            $_SESSION['success'] = "Registration successful. Please log in.";
            header("Location: /login");
            exit;
        } else {
            $_SESSION['errors'] = ["Failed to register user. Please try again."];
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
            $uploadDir = __DIR__ . '/../../uploads/profile_pictures/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Check file type and size
            if ($file['error'] === UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg', 'image/png'];
                if (!in_array($file['type'], $allowedTypes)) {
                    $validation['errors'][] = "Invalid file type for profile picture. Only JPG and PNG are allowed.";
                } elseif ($file['size'] > 2 * 1024 * 1024) { // 2MB limit
                    $validation['errors'][] = "Profile picture size must not exceed 2MB.";
                } else {
                    $profilePicturePath = $uploadDir . basename($file['name']);
                    move_uploaded_file($file['tmp_name'], $profilePicturePath);
                }
            } else {
                $validation['errors'][] = "Profile picture is required for lecturers.";
            }
        }
        return array($profilePicturePath, $validation);
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
}
