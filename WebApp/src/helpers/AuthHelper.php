<?php

namespace helpers;

use Random\RandomException;

class AuthHelper
{
    /**
     * Hash a password securely using the bcrypt algorithm.
     *
     * @param string $password The plain text password.
     * @return string The hashed password.
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify a password against a hashed password.
     *
     * @param string $password The plain text password.
     * @param string $hashedPassword The stored hashed password.
     * @return bool True if the password is correct, false otherwise.
     */
    public static function verifyPassword(string $password, string $hashedPassword): bool
    {
        return password_verify($password, $hashedPassword);
    }

    /**
     * Ensure a password is hashed (if not already hashed).
     *
     * @param string $password The password to check and hash if needed.
     * @return string The hashed password.
     */
    public static function ensurePasswordHashed(string $password): string
    {
        // Check if password is already hashed (PHP stores algorithm metadata)
        return password_get_info($password)['algo'] !== null ? $password : password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Start a PHP session if it is not already active.
     */
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Log in a user and store user data in the session.
     *
     * @param array $user The user data (must contain 'id', 'name', and 'role').
     */
    public static function loginUser(array $user): void
    {
        self::startSession();
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'role' => $user['role']
        ];
        $_SESSION['last_activity'] = time(); // Track last activity for session timeout

        Logger::info("User logged in: " . var_export($_SESSION, true));
    }

    /**
     * Check if a user is logged in.
     *
     * @return bool True if a user is logged in, false otherwise.
     */
    public static function isLoggedIn(): bool
    {
        self::startSession();
        return isset($_SESSION['user']['id']);
    }

    /**
     * Log out the current user and destroy the session.
     */
    public static function logoutUser(): void
    {
        self::startSession();
        session_destroy();
        unset($_SESSION);

        // Clear session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        Logger::info("User logged out.");
    }

    /**
     * Check if the logged-in user has a specific role.
     *
     * @param string $role The required role.
     * @return bool True if the user has the role, false otherwise.
     */
    public static function isRole(string $role): bool
    {
        self::startSession();
        return isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === $role;
    }

    /**
     * Get the logged-in user's ID.
     *
     * @return int|null The user ID or null if not logged in.
     */
    public static function getUserId(): ?int
    {
        self::startSession();
        return $_SESSION['user']['id'] ?? null;
    }

    /**
     * Get the logged-in user's role.
     *
     * @return string|null The user role or null if not logged in.
     */
    public static function getUserRole(): ?string
    {
        self::startSession();
        return $_SESSION['user']['role'] ?? null;
    }

    /**
     * Redirect to a specific page if the user is not logged in.
     *
     * @param string $redirectUrl The URL to redirect to.
     */
    public static function requireLogin(string $redirectUrl = '/login'): void
    {
        if (!self::isLoggedIn()) {
            header("Location: $redirectUrl");
            exit;
        }
    }

    /**
     * Redirect to a specific page if the user does not have the required role.
     *
     * @param string $role The required role.
     * @param string $redirectUrl The URL to redirect to.
     */
    public static function requireRole(string $role, string $redirectUrl = '/unauthorized'): void
    {
        if (!self::isRole($role)) {
            header("Location: $redirectUrl");
            exit;
        }
    }

    /**
     * Enforce session timeout and log out users after inactivity.
     *
     * @param int $timeout The timeout duration in seconds (default: 1800s = 30 minutes).
     */
    public static function enforceSessionTimeout(int $timeout = 1800): void
    {
        self::startSession();
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
            self::logoutUser();
            header("Location: /login?timeout=true");
            exit;
        }
        $_SESSION['last_activity'] = time();
    }

    /**
     * Generate a CSRF token for protection against Cross-Site Request Forgery attacks.
     *
     * @return string The generated CSRF token.
     * @throws RandomException
     */
    public static function generateCsrfToken(): string
    {
        self::startSession();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify a CSRF token to protect against CSRF attacks.
     *
     * @param string $token The CSRF token to verify.
     */
    public static function verifyCsrfToken(string $token): void
    {
        self::startSession();
        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $token) {
            self::logoutUser();
            header("Location: /login?csrf_error=true");
            exit;
        }
    }
}
