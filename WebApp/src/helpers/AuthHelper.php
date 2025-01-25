<?php

class AuthHelper
{
    // Hash a password securely
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    // Verify a password against a hash
    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    // Start a session (if not already started)
    public static function startSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Log in a user (store user data in session)
    public static function loginUser($userId, $role)
    {
        self::startSession();
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_role'] = $role;
    }

    // Check if a user is logged in
    public static function isLoggedIn()
    {
        self::startSession();
        return isset($_SESSION['user_id']);
    }

    // Log out the current user
    public static function logoutUser()
    {
        self::startSession();
        session_destroy();
        unset($_SESSION);
    }

    // Check if a user has a specific role
    public static function isRole($role)
    {
        self::startSession();
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
    }

    // Get the logged-in user's ID
    public static function getUserId()
    {
        self::startSession();
        return $_SESSION['user_id'] ?? null;
    }

    // Get the logged-in user's role
    public static function getUserRole()
    {
        self::startSession();
        return $_SESSION['user_role'] ?? null;
    }

    // Redirect to a specific page if the user is not logged in
    public static function requireLogin($redirectUrl = '/login')
    {
        if (!self::isLoggedIn()) {
            header("Location: $redirectUrl");
            exit;
        }
    }

    // Redirect to a specific page if the user does not have the required role
    public static function requireRole($role, $redirectUrl = '/unauthorized')
    {
        if (!self::isRole($role)) {
            header("Location: $redirectUrl");
            exit;
        }
    }
}
