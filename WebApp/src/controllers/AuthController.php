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
        $input = ApiHelper::getJsonInput();

        // Combine first_name and last_name into a single name field
        if (!empty($input['first_name']) && !empty($input['last_name'])) {
            $input['name'] = $input['first_name'] . ' ' . $input['last_name'];
        } else {
            $input['name'] = ''; // Ensure the name field exists for validation
        }

        // Validation rules
        $validationRules = [
            'first_name' => ['required' => true, 'min' => 3, 'max' => 50],
            'last_name' => ['required' => true, 'min' => 3, 'max' => 50],
            'name' => ['required' => true, 'min' => 3, 'max' => 100], // Added name validation
            'email' => ['required' => true, 'email' => true],
            'password' => ['required' => true, 'password' => true],
            'repeat_password' => ['required' => true], // Optional: Add match validation below
            'role' => ['required' => true],
            'study_program' => ['required' => false, 'max' => 100],
            'cohort_year' => ['required' => false, 'integer' => true],
        ];

        // Validate inputs
        $validation = InputValidator::validateInputs($input, $validationRules);

        if (!empty($validation['errors'])) {
            Logger::error("Registration failed: Validation errors.", $validation['errors']);
            ApiHelper::sendError(400, 'Validation failed.', $validation['errors']);
        }

        $sanitized = $validation['sanitized'];

        // Check if passwords match
        if ($sanitized['password'] !== $sanitized['repeat_password']) {
            Logger::error("Registration failed: Passwords do not match.");
            ApiHelper::sendError(400, 'Passwords do not match.');
        }

        // Check if the email already exists
        if ($this->userModel->getUserByEmail($sanitized['email'])) {
            Logger::error("Registration failed: Email already registered.");
            ApiHelper::sendError(400, 'Email is already registered.');
        }

        // Hash password and create user
        $hashedPassword = AuthHelper::hashPassword($sanitized['password']);
        if ($this->userModel->createUser(
            $sanitized['name'], // Pass validated and sanitized name
            $sanitized['email'],
            $hashedPassword,
            $sanitized['role'],
            $sanitized['study_program'] ?? null,
            $sanitized['cohort_year'] ?? null
        )) {
            Logger::info("User registered successfully: " . $sanitized['email']);
            ApiHelper::sendResponse(201, [], 'User registered successfully.');
        } else {
            Logger::error("Failed to create user in the database.");
            ApiHelper::sendError(500, 'Failed to register user.');
        }
    }



    // User Login
    public function login()
    {
        $input = ApiHelper::getJsonInput();

        // Validate input
        ApiHelper::validateRequest(['email', 'password'], $input);

        $user = $this->userModel->getUserByEmail($input['email']);
        if (!$user || !AuthHelper::verifyPassword($input['password'], $user['password'])) {
            Logger::error("Login failed for email: " . $input['email']);
            ApiHelper::sendError(401, 'Invalid email or password.');
        }

        // Log in the user
        AuthHelper::loginUser($user['id'], $user['role']);
        Logger::info("User logged in: " . $input['email']);
        ApiHelper::sendResponse(200, ['user_id' => $user['id'], 'role' => $user['role']], 'Login successful.');
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
}
