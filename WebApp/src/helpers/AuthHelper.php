<?php



class AuthHelper
{
    // Hash a password securely
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    // Verify a password against a hash
    public static function verifyPassword($password, $hashPassword)
    {
        return password_verify($password, $hashPassword);
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
        $_SESSION['last_activity'] = time(); // For session timeout
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

    // Enforce session timeout
    public static function enforceSessionTimeout($timeout = 1800)
    {
        self::startSession();
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
            self::logoutUser();
            header("Location: /login?timeout=true");
            exit;
        }
        $_SESSION['last_activity'] = time();
    }

    // Generate a CSRF token
    public static function generateCsrfToken()
    {
        self::startSession();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    // Verify a CSRF token
    public static function verifyCsrfToken($token)
    {
        self::startSession();
        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $token) {
            self::logoutUser();
            header("Location: /login?csrf_error=true");
            exit;
        }
    }
}
