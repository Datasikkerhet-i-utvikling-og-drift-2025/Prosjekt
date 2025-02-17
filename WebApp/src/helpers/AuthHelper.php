<?php



class AuthHelper
{
    // Hash a password securely
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    // Verify a password against a hash
    public static function verifyPassword($password, $hashedPassword)
    {
        return password_verify($password, $hashedPassword);
    }

    // Start a session (if not already started)
    public static function startSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Log in a user (store user data in session)
    public static function loginUser($user)
    {
        self::startSession();
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'role' => $user['role']
        ];
        Logger::info("User session started: " . var_export($_SESSION, true));
    }

    // Check if a user is logged in
    public static function isLoggedIn()
    {
        self::startSession();
        return isset($_SESSION['user']['id']);
    }

    // Log out the current user
    public static function logoutUser()
    {
        Logger::info("something is happening");
        self::startSession();
        session_destroy();
        unset($_SESSION);
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
    }

    // Check if a user has a specific role
    public static function isRole($role)
    {
        self::startSession();
        return isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === $role;
    }

    // Get the logged-in user's ID
    public static function getUserId()
    {
        self::startSession();
        return $_SESSION['user']['id'] ?? null;
    }

    // Get the logged-in user's role
    public static function getUserRole()
    {
        self::startSession();
        return $_SESSION['user']['role'] ?? null;
    }

    // Redirect to a specific page if the user is not logged in
    public static function requireLogin($redirectUrl = '/')
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
