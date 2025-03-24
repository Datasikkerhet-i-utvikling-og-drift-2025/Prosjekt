<?php

namespace managers;

use helpers\Logger;

/**
 * Class SessionManager
 * Handles secure session management including fingerprinting,
 * idle timeout, absolute lifetime, and brute-force protection.
 */
class SessionManager
{
    private int $absoluteLifetime = 2592000; // 30 dager
    private int $idleTimeout = 1800;         // 30 minutter
    private int $maxFailedAttempts = 3;      // Maks 3 mislykkede innlogginger

    /**
     * SessionManager constructor.
     * Starts secure session and validates its integrity.
     */
    public function __construct()
    {
        $this->startSecureSession();
        $this->validateSession();
    }

    /**
     * Starts a secure PHP session with appropriate cookie flags.
     */
    private function startSecureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => $this->absoluteLifetime,
                'path' => '/',
                'domain' => $_SERVER['HTTP_HOST'] ?? '',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
            session_start();
            Logger::info("Secure session started.");
        }
    }

    /**
     * Validates session fingerprint, inactivity timeout, and expiration.
     * Destroys session if integrity checks fail.
     */
    private function validateSession(): void
    {
        $currentFingerprint = $this->generateFingerprint();

        if (isset($_SESSION['fingerprint']) && $_SESSION['fingerprint'] !== $currentFingerprint) {
            Logger::warning("Session fingerprint mismatch. Destroying session.");
            $this->destroy();
            return;
        }

        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
            $_SESSION['last_activity'] = time();
            $_SESSION['fingerprint'] = $currentFingerprint;
            $_SESSION['failed_attempts'] = 0;
            Logger::info("New session initialized.");
            return;
        }

        if (time() - $_SESSION['created'] > $this->absoluteLifetime) {
            Logger::info("Session expired (absolute lifetime).");
            $this->destroy();
            return;
        }

        if (time() - $_SESSION['last_activity'] > $this->idleTimeout) {
            Logger::info("Session expired (inactivity).");
            $this->destroy();
            return;
        }

        $_SESSION['last_activity'] = time();
    }

    /**
     * Generates a fingerprint hash based on user agent, IP, and language.
     *
     * @return string SHA-256 fingerprint
     */
    private function generateFingerprint(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        return hash('sha256', $ip . $ua . $lang);
    }

    /**
     * Sets a value in the session.
     *
     * @param string $key
     * @param mixed $value
     */
    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Gets a value from the session.
     *
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key): mixed
    {
        return $_SESSION[$key] ?? null;
    }

    /**
     * Stores user credentials in the session.
     *
     * @param array $user Associative array with keys: id, email, role
     * @param string|null $token JWT token if applicable
     */
    public function storeUser(array $user, ?string $token = null): void
    {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role']
        ];
        if ($token) {
            $_SESSION['token'] = $token;
        }
        Logger::info("User stored in session.");
    }

    /**
     * Returns true if a user is authenticated.
     *
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return isset($_SESSION['user']['id'], $_SESSION['fingerprint']);
    }

    /**
     * Gets the current user's role.
     *
     * @return string|null
     */
    public function getUserRole(): ?string
    {
        return $_SESSION['user']['role'] ?? null;
    }

    /**
     * Destroys the current session and removes all data.
     */
    public function destroy(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        Logger::info("Session destroyed.");
    }

    /**
     * Tracks failed login attempts in session.
     */
    public function incrementFailedLogin(): void
    {
        $_SESSION['failed_attempts'] = ($_SESSION['failed_attempts'] ?? 0) + 1;
        Logger::warning("Failed login attempt #{$_SESSION['failed_attempts']}");
    }

    /**
     * Checks if user has exceeded failed login limit.
     *
     * @return bool
     */
    public function tooManyFailedAttempts(): bool
    {
        return ($_SESSION['failed_attempts'] ?? 0) >= $this->maxFailedAttempts;
    }
}
