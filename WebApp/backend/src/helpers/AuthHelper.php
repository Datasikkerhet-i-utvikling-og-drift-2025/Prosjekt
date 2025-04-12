<?php

namespace helpers;

    /**
     * Class AuthHelper
     * Provides secure password hashing and verification utilities.
     */
class AuthHelper
{
    private static GrayLogger $logger;

    public static function initLogger(): void
    {
        self::$logger = GrayLogger::getInstance();
    }

    /**
     * Securely hash a password.
     *
     * @param string $password
     * @return string
     */
    public static function hashPassword(string $password): string
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        self::$logger->debug("Hashing password", [
            'plain' => $password,
            'hashed password' => $hashedPassword,
        ]);
        return $hashedPassword;
    }

    /**
     * Verify a hashed password.
     *
     * @param string $password
     * @param string $hashedPassword
     * @return bool
     */
    public static function verifyPassword(string $password, string $hashedPassword): bool
    {
        $result = password_verify($password, $hashedPassword);
        if (!$result) {
            self::$logger->debug("Password verification failed", [
                'plain' => $password,
                'hash' => $hashedPassword,
            ]);
        }
        return $result;
    }


    /**
     * Check if a password is already hashed.
     *
     * @param string $password
     * @return string
     */
    public static function ensurePasswordHashed(string $password): string
    {
        return password_get_info($password)['algo'] !== null
            ? $password
            : password_hash($password, PASSWORD_DEFAULT);
    }
}