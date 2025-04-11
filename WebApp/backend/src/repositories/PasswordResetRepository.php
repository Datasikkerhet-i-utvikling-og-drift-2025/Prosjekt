<?php

namespace repositories; // Tilpass namespace

use managers\DatabaseManager;
use PDO;

/**
 * Repository for handling password reset token database operations.
 * Stores a HASH of the token for security.
 */
class PasswordResetRepository
{
    private DatabaseManager $db;
    private const HASH_ALGO = 'sha256'; // Algoritme for å hashe tokenet

    /**
     * PasswordResetRepository constructor.
     * @param PDO $db A PDO database connection instance.
     */
    public function __construct(DatabaseManager $db)
    {
        $this->db = $db;
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Hashes the raw token for storage/lookup.
     * @param string $token The raw token.
     * @return string The hashed token.
     */
    private function hashToken(string $token): string
    {
        return hash(self::HASH_ALGO, $token);
    }

    /**
     * Finds password reset token data by the *raw* token (hashes it for lookup).
     *
     * @param string $rawToken The *raw* token provided by the user (from URL).
     * @return array|false Token data (user_id, expires_at) or false if not found/error.
     */
    public function findByToken(string $rawToken): array|false
    {
        $hashedToken = $this->hashToken($rawToken);
        $sql = "SELECT user_id, expires_at FROM password_resets WHERE token = :hashed_token LIMIT 1";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':hashed_token', $hashedToken, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Konverter expires_at til DateTime-objekt hvis funnet
            if ($result && isset($result['expires_at'])) {
                try {
                    // Anta at databasen lagrer i et format DateTime kan parse
                    $result['expires_at'] = new DateTime($result['expires_at']);
                } catch (\Exception $e) {
                    error_log("Error parsing expires_at date: " . $e->getMessage());
                    return false; // Feil ved datokonvertering
                }
            }
            return $result; // Returnerer data-array eller false

        } catch (PDOException $e) {
            error_log("Database Error (findByToken): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Stores a new password reset token (hashes it first).
     *
     * @param int $userId The ID of the user requesting the reset.
     * @param string $rawToken The *raw* token to store (will be hashed).
     * @param DateTime $expiresAt The expiry time of the token.
     * @return bool True on success, false on failure.
     */
    public function storeToken(int $userId, string $rawToken, DateTime $expiresAt): bool
    {
        $hashedToken = $this->hashToken($rawToken);
        $expiresAtFormatted = $expiresAt->format('Y-m-d H:i:s'); // Standard DB format

        $sql = "INSERT INTO password_resets (user_id, token, expires_at) VALUES (:user_id, :hashed_token, :expires_at)";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':hashed_token', $hashedToken, PDO::PARAM_STR);
            $stmt->bindValue(':expires_at', $expiresAtFormatted, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Database Error (storeToken): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes a specific password reset token using the *raw* token.
     *
     * @param string $rawToken The *raw* token to delete.
     * @return bool True on success or if token didn't exist, false on error.
     */
    public function deleteToken(string $rawToken): bool
    {
        $hashedToken = $this->hashToken($rawToken);
        $sql = "DELETE FROM password_resets WHERE token = :hashed_token";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':hashed_token', $hashedToken, PDO::PARAM_STR);
            $stmt->execute();
            return true; // Returner true selv om ingen rader ble slettet
        } catch (PDOException $e) {
            error_log("Database Error (deleteToken): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes all existing password reset tokens for a specific user.
     * Useful before creating a new token for the user.
     *
     * @param int $userId The user's ID.
     * @return bool True on success, false on error.
     */
    public function deleteTokensForUser(int $userId): bool
    {
        $sql = "DELETE FROM password_resets WHERE user_id = :user_id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return true; // Returner true selv om ingen rader ble slettet
        } catch (PDOException $e) {
            error_log("Database Error (deleteTokensForUser): " . $e->getMessage());
            return false;
        }
    } }