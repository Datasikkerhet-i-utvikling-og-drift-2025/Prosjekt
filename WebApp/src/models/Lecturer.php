<?php

namespace models;

use DateMalformedStringException;
use PDO;
use PDOStatement;

require_once __DIR__ . '/../helpers/InputValidator.php';
require_once __DIR__ . '/../helpers/Logger.php';

class Lecturer extends User
{
    public string $imagePath {
        get {
            return $this->imagePath;
        }
        set {
            $this->imagePath = $value;
        }
    }

    /**
     * Constructs a new Lecturer instance.
     *
     * This constructor initializes a lecturer object with general user attributes
     * from the `User` class and adds lecturer-specific attributes like imagePath.
     *
     * @param array $userData Associative array containing lecturer data.<br>
     *        - `id` (int|null) User ID (if null, assigned by the database).<br>
     *        - `firstName` (string) lecturer's first name.<br>
     *        - `lastName` (string) lecturer's last name.<br>
     *        - `email` (string) lecturer's email address.<br>
     *        - `password` (string) lecturer's password (hashed or plaintext).<br>
     *        - `role` (UserRole) The lecturer's role in the system.<br>
     *        - `resetToken` (string|null) Optional reset token for password recovery.<br>
     *        - `resetTokenCreatedAt` (string|null) Timestamp of password reset request.<br>
     *        - `createdAt` (string|null) Timestamp when the lecturer account was created.<br>
     *        - `updatedAt` (string|null) Timestamp of last lecturer account update.<br>
     *        - `imagePath` (string) The path to where the profile picture is stored.<br>
     *
     * @throws DateMalformedStringException
     */
    public function __construct(array $userData)
    {
        parent::__construct($userData);
        $this->imagePath = $userData['imagePath'];
    }


    /**
     * Binds the user's properties as parameters for a prepared PDO statement.
     *
     * This method ensures that all relevant user attributes are securely bound to a
     * prepared SQL statement before execution, reducing the risk of SQL injection.
     *
     * @param PDOStatement $stmt The prepared statement to which user attributes will be bound.
     *
     * @return void
     */
    public function bindUserDataForDbStmt(PDOStatement $stmt): void
    {
        $stmt->bindValue(':id', $this->id ?? null, $this->id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':first_name', $this->firstName, PDO::PARAM_STR);
        $stmt->bindValue(':last_name', $this->lastName, PDO::PARAM_STR);
        $stmt->bindValue(':full_name', $this->fullName, PDO::PARAM_STR);
        $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
        $stmt->bindValue(':password', $this->password, PDO::PARAM_STR);
        $stmt->bindValue(':role', $this->role->value, PDO::PARAM_STR);
        $stmt->bindValue(':imagePath', $this->imagePath, PDO::PARAM_STR );
    }

}

    // Get all courses taught by the lecturer
    public function getCourses($lecturerId)
    {
        $sql = "SELECT id, code, name, pin_code, created_at FROM courses WHERE lecturer_id = :lecturerId";
        $stmt = $this->pdo->prepare($sql);

        try {
            $stmt->execute([':lecturerId' => $lecturerId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Logger::error("Failed to fetch courses for lecturer ID $lecturerId: " . $e->getMessage());
            return [];
        }
    }

    // Get all messages for a specific course
    public function getMessagesForCourse($courseId)
    {
        $sql = "SELECT m.id AS message_id, m.content, m.reply, m.created_at, m.anonymous_id
                FROM messages m
                WHERE m.course_id = :courseId";
        $stmt = $this->pdo->prepare($sql);

        try {
            $stmt->execute([':courseId' => $courseId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Logger::error("Failed to fetch messages for course ID $courseId: " . $e->getMessage());
            return [];
        }
    }

    // Reply to a student's message
    public function replyToMessage($messageId, $replyContent)
    {
        if (!InputValidator::isNotEmpty($replyContent)) {
            Logger::error("Reply content is empty for message ID $messageId");
            return false;
        }

        $sql = "UPDATE messages SET reply = :replyContent, updated_at = NOW() WHERE id = :messageId";
        $stmt = $this->pdo->prepare($sql);

        try {
            return $stmt->execute([
                ':messageId' => $messageId,
                ':replyContent' => InputValidator::sanitizeString($replyContent),
            ]);
        } catch (PDOException $e) {
            Logger::error("Failed to reply to message ID $messageId: " . $e->getMessage());
            return false;
        }
    }

    // View a specific message by ID (for detailed inspection)
    public function getMessageById($messageId)
    {
        $sql = "SELECT m.id AS message_id, m.content, m.reply, m.created_at, m.anonymous_id
                FROM messages m
                WHERE m.id = :messageId";
        $stmt = $this->pdo->prepare($sql);

        try {
            $stmt->execute([':messageId' => $messageId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Logger::error("Failed to fetch message ID $messageId: " . $e->getMessage());
            return null;
        }
    }

    // Report an inappropriate message
    public function reportMessage($messageId, $reason)
    {
        if (!InputValidator::isNotEmpty($reason)) {
            Logger::error("Report reason is empty for message ID $messageId");
            return false;
        }

        $sql = "INSERT INTO reports (message_id, report_reason, created_at)
                VALUES (:messageId, :reason, NOW())";
        $stmt = $this->pdo->prepare($sql);

        try {
            return $stmt->execute([
                ':messageId' => $messageId,
                ':reason' => InputValidator::sanitizeString($reason),
            ]);
        } catch (PDOException $e) {
            Logger::error("Failed to report message ID $messageId: " . $e->getMessage());
            return false;
        }
    }
}
