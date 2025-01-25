<?php

require_once '../src/helpers/ApiHelper.php';
require_once '../src/helpers/AuthHelper.php';
require_once '../src/helpers/InputValidator.php';
require_once '../src/models/User.php';

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

        // Validate input
        $validationRules = [
            'name' => ['required' => true, 'min' => 3, 'max' => 50],
            'email' => ['required' => true, 'email' => true],
            'password' => ['required' => true, 'password' => true],
            'role' => ['required' => true]
        ];
        $validation = InputValidator::validateInputs($input, $validationRules);

        if (!empty($validation['errors'])) {
            ApiHelper::sendError(400, 'Validation failed.', $validation['errors']);
        }

        $sanitized = $validation['sanitized'];

        // Check if the email already exists
        if ($this->userModel->getUserByEmail($sanitized['email'])) {
            ApiHelper::sendError(400, 'Email is already registered.');
        }

        // Hash password and create user
        $hashedPassword = AuthHelper::hashPassword($sanitized['password']);
        $this->userModel->createUser(
            $sanitized['name'],
            $sanitized['email'],
            $hashedPassword,
            $sanitized['role']
        );

        ApiHelper::sendResponse(201, [], 'User registered successfully.');
    }

    // User Login
    public function login()
    {
        $input = ApiHelper::getJsonInput();

        // Validate input
        ApiHelper::validateRequest(['email', 'password'], $input);

        $user = $this->userModel->getUserByEmail($input['email']);
        if (!$user || !AuthHelper::verifyPassword($input['password'], $user['password'])) {
            ApiHelper::sendError(401, 'Invalid email or password.');
        }

        // Log in the user
        AuthHelper::loginUser($user['id'], $user['role']);
        ApiHelper::sendResponse(200, ['user_id' => $user['id'], 'role' => $user['role']], 'Login successful.');
    }

    // User Logout
    public function logout()
    {
        AuthHelper::logoutUser();
        ApiHelper::sendResponse(200, [], 'User logged out successfully.');
    }

    // Password Reset Request
    public function requestPasswordReset()
    {
        $input = ApiHelper::getJsonInput();
        ApiHelper::validateRequest(['email'], $input);

        $user = $this->userModel->getUserByEmail($input['email']);
        if (!$user) {
            ApiHelper::sendError(404, 'Email not found.');
        }

        // Generate reset token and link
        $resetToken = bin2hex(random_bytes(16));
        $resetLink = "https://yourapp.com/reset-password?token=$resetToken";

        // Save the reset token
        $this->userModel->savePasswordResetToken($user['id'], $resetToken);

        // Send password reset email
        $mailer = new Mailer();
        if ($mailer->sendPasswordReset($input['email'], $resetLink)) {
            ApiHelper::sendResponse(200, [], 'Password reset email sent successfully.');
        } else {
            ApiHelper::sendError(500, 'Failed to send password reset email.');
        }
    }

    // Password Reset
    public function resetPassword()
    {
        $input = ApiHelper::getJsonInput();
        ApiHelper::validateRequest(['token', 'new_password'], $input);

        // Validate token and reset password
        $user = $this->userModel->getUserByResetToken($input['token']);
        if (!$user) {
            ApiHelper::sendError(400, 'Invalid or expired token.');
        }

        // Update password
        $hashedPassword = AuthHelper::hashPassword($input['new_password']);
        $this->userModel->updatePassword($user['id'], $hashedPassword);

        ApiHelper::sendResponse(200, [], 'Password reset successfully.');
    }
}
