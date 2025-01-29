<?php

require_once 'User.php';
require_once __DIR__ . '/../helpers/InputValidator.php';
require_once __DIR__ . '/../helpers/Logger.php';

class Lecturer extends User
{
    public function __construct($pdo)
    {
        parent::__construct($pdo);
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
