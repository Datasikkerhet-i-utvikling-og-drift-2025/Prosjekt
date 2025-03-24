<?php

namespace helpers;

    /**
     * Class AuthHelper
     * Provides secure password hashing and verification utilities.
     */
class AuthHelper
{
    /**
     * Securely hash a password.
     *
     * @param string $password
     * @return string
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
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
        return password_verify($password, $hashedPassword);
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