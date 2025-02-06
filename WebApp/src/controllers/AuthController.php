<?php

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
        $this->pdo = $pdo;  // Lagre PDO-tilkoblingen
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
            $uploadDir = __DIR__ . '/../../uploads/profile_pictures/';
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $uploadDir));
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
            //header("Location: /register");
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
