<?php

namespace controllers;

require_once __DIR__ . '/../helpers/ApiHelper.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';
require_once __DIR__ . '/../helpers/InputValidator.php';
require_once __DIR__ . '/../helpers/Logger.php';
require_once __DIR__ . '/../helpers/Mailer.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../models/User.php';

use DateMalformedStringException;
use Exception;
use factories\UserFactory;
use helpers\ApiHelper;
use helpers\AuthHelper;
use helpers\InputValidator;
use helpers\Logger;
use JetBrains\PhpStorm\NoReturn;
use JsonException;
use Mailer;
use Random\RandomException;
use repositories\UserRepository;
use RuntimeException;
use service\DatabaseService;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AuthController
{
    private UserRepository $userRepository;

    /**
     * Constructor: Initializes the AuthController with a UserRepository instance.
     *
     * @param DatabaseService $db to handle the interaction with database
     */
    public function __construct(DatabaseService $db)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->userRepository = new UserRepository($db);
    }

    /**
     * Register a new user.
     *
     * @return void
     * @throws JsonException|DateMalformedStringException
     */
    #[NoReturn] public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            ApiHelper::sendError(405, 'Invalid request method.');
        }

        $input = $_POST;
        $input['name'] = trim(($input['first_name'] ?? '') . ' ' . ($input['last_name'] ?? ''));
        $validation = InputValidator::validateRegistration($input);

        // Check for validation errors
        if (!empty($validation['errors'])) {
            ApiHelper::sendError(400, 'Validation failed.', $validation['errors']);
        }

        // Hash the password securely
        $hashedPassword = AuthHelper::hashPassword($validation['sanitized']['password']);

        // Handle profile picture upload if applicable
        [$profilePicturePath, $validation] = $this->profilePictureUpload($validation);
        $validation['sanitized']['image_path'] = $profilePicturePath ?? '';

        // Check if the email is already registered
        if ($this->userRepository->getUserByEmail($validation['sanitized']['email'])) {
            ApiHelper::sendError(409, 'Email is already registered.');
        }

        $user = UserFactory::createUser($validation['sanitized']);

        if (!$this->userRepository->createUser($user)) {
            ApiHelper::sendError(500, 'Failed to create user.');
        }

        // **Handle Web vs API Request**
        if (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            // API request (mobile app)
            ApiHelper::sendResponse(201, ['redirect' => '/'], 'Registration successful.');
        } else {
            // Web request (form submission)
            $_SESSION['success'] = 'Registration successful.';
            header("Location: /");
            exit();
        }
    }


    /**
     * Handles profile picture upload for lecturers.
     *
     * @param array $validation The validation array containing sanitized user input.
     * @return array Returns an array containing the profile picture path (or null) and the validation array.
     */
    public function profilePictureUpload(array $validation): array
    {
        $profilePicturePath = null;

        if ($validation['sanitized']['role'] === 'lecturer' && isset($_FILES['profile_picture'])) {
            $file = $_FILES['profile_picture'];
            $uploadDir = __DIR__ . '/../../public/uploads/profile_pictures/';

            // Ensure upload directory exists
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $uploadDir));
            }

            // Validate file type and size
            $allowedTypes = ['image/jpeg', 'image/png'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $validation['errors'][] = "Profile picture upload failed.";
                Logger::error("Profile picture upload error: " . var_export($file, true));
            } elseif (!in_array($file['type'], $allowedTypes, true)) {
                $validation['errors'][] = "Invalid file type for profile picture. Only JPG and PNG are allowed.";
                Logger::error("Invalid profile picture type: " . $file['type']);
            } elseif ($file['size'] > 2 * 1024 * 1024) { // 2MB limit
                $validation['errors'][] = "Profile picture size must not exceed 2MB.";
                Logger::error("Profile picture size exceeds limit: " . $file['size'] . " bytes");
            } else {
                // Generate a unique filename
                $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $uniqueFileName = uniqid('profile_', true) . '.' . $fileExtension;
                $profilePicturePath = $uploadDir . $uniqueFileName;

                if (!move_uploaded_file($file['tmp_name'], $profilePicturePath)) {
                    $validation['errors'][] = "Failed to save profile picture.";
                    Logger::error("Failed to move uploaded file for profile picture.");
                }
            }

            return ['/uploads/profile_pictures/' . $uniqueFileName, $validation];
        }

        return [$profilePicturePath, $validation];
    }


    /**
     * Authenticate and log in a user.
     *
     * @return void
     * @throws JsonException|DateMalformedStringException
     */
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            ApiHelper::sendError(405, 'Invalid request method.');
        }

        $input = ApiHelper::getJsonInput();
        ApiHelper::validateRequest(['email', 'password'], $input);

        $user = $this->userRepository->getUserByEmail($input['email']);

        if (!$user || !AuthHelper::verifyPassword($input['password'], $user->password)) {
            Logger::error("Login failed for email: " . $input['email']);
            ApiHelper::sendError(401, 'Invalid email or password.');
        }

        // Log the user in and create a session
        AuthHelper::loginUser((array)$user);
        Logger::info("User logged in: " . $input['email']);

        // Redirect user based on their role
        $redirectUrl = match ($user->role->value) {
            'student' => '/student/dashboard',
            'lecturer' => '/lecturer/dashboard',
            'admin' => '/admin/dashboard',
            default => '/' . ApiHelper::sendError(400, 'Unknown user role.')
        };

        ApiHelper::sendResponse(200, ['redirect' => $redirectUrl], 'Login successful.');
    }

    /**
     * Log out the current user.
     *
     * @return void
     * @throws JsonException
     */
    public function logout()
    {
        AuthHelper::logoutUser();
        Logger::info("User logged out.");
        ApiHelper::sendResponse(200, [], 'Logout successful.');
    }

    /**
     * Change the password of a logged-in user.
     *
     * @return void
     * @throws JsonException|DateMalformedStringException
     */
    public function changePassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            ApiHelper::sendError(405, 'Invalid request method.');
        }

        $input = ApiHelper::getJsonInput();
        ApiHelper::validateRequest(['current_password', 'new_password', 'confirm_password'], $input);

        $userId = AuthHelper::getUserId();
        $user = $this->userRepository->getUserById($userId);

        if (!$user || !AuthHelper::verifyPassword($input['current_password'], $user->password)) {
            ApiHelper::sendError(403, 'Current password is incorrect.');
        }

        if ($input['new_password'] !== $input['confirm_password']) {
            ApiHelper::sendError(400, 'New passwords do not match.');
        }

        $hashedPassword = AuthHelper::hashPassword($input['new_password']);
        $this->userRepository->updatePassword($userId, $hashedPassword);

        ApiHelper::sendResponse(200, [], 'Password updated successfully.');
    }

public function requestPasswordReset()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /reset-password');
        exit;
    }

    $email = $_POST['email'] ?? '';

    // Find user by email
    $user = $this->userModel->getUserByEmail($email);
    if (!$user) {
        header('Location: /reset-password?error=' . urlencode('Email not found'));
        exit;
    }

    // Generate reset token
    $resetToken = bin2hex(random_bytes(32));

    // Save token to database
    if ($this->userModel->savePasswordResetToken($user['id'], $resetToken)) {
        // Send reset email
        $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/reset-password?token=" . $resetToken;
        // Her bør du implementere email-sending

        header('Location: /login?success=' . urlencode('Password reset instructions sent to your email'));
    } else {
        header('Location: /reset-password?error=' . urlencode('Failed to process reset request'));
    }
    exit;
}

    /**
     * Reset user password using a token.
     *
     * @return void
     * @throws JsonException
     */
    public function resetPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            ApiHelper::sendError(405, 'Invalid request method.');
        }

        $input = ApiHelper::getJsonInput();
        ApiHelper::validateRequest(['token', 'new_password', 'confirm_password'], $input);

        if ($input['new_password'] !== $input['confirm_password']) {
            ApiHelper::sendError(400, 'Passwords do not match.');
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

        $hashedPassword = AuthHelper::hashPassword($input['new_password']);
        $this->userRepository->updatePasswordAndClearToken($user->id, $hashedPassword);

        ApiHelper::sendResponse(200, [], 'Password reset successful.');
    }
}
