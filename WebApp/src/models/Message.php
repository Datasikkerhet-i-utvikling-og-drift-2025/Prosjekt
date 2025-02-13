<?php

require_once __DIR__ . '/../helpers/InputValidator.php';
require_once __DIR__ . '/../helpers/Logger.php';

class Message
{
    private $pdo; // PDO instance

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Create a new message
    public function createMessage($studentId, $courseId, $anonymousId, $content)
    {
        if (!InputValidator::isNotEmpty($content)) {
            Logger::error("Message content is empty for student ID $studentId and course ID $courseId");
            return false;
        }

        $sql = "INSERT INTO messages (student_id, course_id, anonymous_id, content, created_at)
                VALUES (:studentId, :courseId, :anonymousId, :content, NOW())";
        $stmt = $this->pdo->prepare($sql);

        try {
            return $stmt->execute([
                ':studentId' => (int)$studentId,
                ':courseId' => (int)$courseId,
                ':anonymousId' => InputValidator::sanitizeString($anonymousId),
                ':content' => InputValidator::sanitizeString($content),
            ]);
        } catch (PDOException $e) {
            Logger::error("Failed to create message: " . $e->getMessage());
            return false;
        }
    }

    // Retrieve all messages for a specific course
    public function getMessagesByCourse($courseId)
    {
        if (!InputValidator::isValidInteger($courseId)) {
            Logger::error("Invalid course ID: $courseId");
            return [];
        }

        $sql = "SELECT m.id AS message_id, m.content, m.reply, m.created_at, m.anonymous_id
                FROM messages m
                WHERE m.course_id = :courseId";
        $stmt = $this->pdo->prepare($sql);

        try {
            $stmt->execute([':courseId' => (int)$courseId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Logger::error("Failed to fetch messages for course ID $courseId: " . $e->getMessage());
            return [];
        }
    }

    // Retrieve all messages sent by a specific student
    public function getMessagesByStudent($studentId)
    {
        if (!InputValidator::isValidInteger($studentId)) {
            Logger::error("Invalid student ID: $studentId");
            return [];
        }

        $sql = "SELECT m.id AS message_id, m.content, m.reply, m.created_at, 
                       c.code AS course_code, c.name AS course_name
                FROM messages m
                JOIN courses c ON m.course_id = c.id
                WHERE m.student_id = :studentId";
        $stmt = $this->pdo->prepare($sql);

        try {
            $stmt->execute([':studentId' => (int)$studentId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Logger::error("Failed to fetch messages for student ID $studentId: " . $e->getMessage());
            return [];
        }
    }

    // Retrieve a specific message by ID
    public function getMessageById($messageId)
    {
        if (!InputValidator::isValidInteger($messageId)) {
            Logger::error("Invalid message ID: $messageId");
            return null;
        }

        $sql = "SELECT m.id AS message_id, m.content, m.reply, m.created_at, 
                       c.code AS course_code, c.name AS course_name
                FROM messages m
                JOIN courses c ON m.course_id = c.id
                WHERE m.id = :messageId";
        $stmt = $this->pdo->prepare($sql);

        try {
            $stmt->execute([':messageId' => (int)$messageId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Logger::error("Failed to fetch message ID $messageId: " . $e->getMessage());
            return null;
        }
    }

    // Update the reply to a message
    public function updateMessageReply($messageId, $replyContent)
    {
        if (!InputValidator::isNotEmpty($replyContent)) {
            Logger::error("Reply content is empty for message ID $messageId");
            return false;
        }

        if (!InputValidator::isValidInteger($messageId)) {
            Logger::error("Invalid message ID: $messageId");
            return false;
        }

        $sql = "UPDATE messages SET reply = :replyContent, updated_at = NOW() WHERE id = :messageId";
        $stmt = $this->pdo->prepare($sql);

        try {
            return $stmt->execute([
                ':messageId' => (int)$messageId,
                ':replyContent' => InputValidator::sanitizeString($replyContent),
            ]);
        } catch (PDOException $e) {
            Logger::error("Failed to update reply for message ID $messageId: " . $e->getMessage());
            return false;
        }
    }

    // Report a message as inappropriate
    public function reportMessage($messageId, $reason)
    {
        if (!InputValidator::isNotEmpty($reason)) {
            Logger::error("Report reason is empty for message ID $messageId");
            return false;
        }

        if (!InputValidator::isValidInteger($messageId)) {
            Logger::error("Invalid message ID: $messageId");
            return false;
        }

        $sql = "INSERT INTO reports (message_id, report_reason, created_at)
                VALUES (:messageId, :reason, NOW())";
        $stmt = $this->pdo->prepare($sql);

        try {
            return $stmt->execute([
                ':messageId' => (int)$messageId,
                ':reason' => InputValidator::sanitizeString($reason),
            ]);
        } catch (PDOException $e) {
            Logger::error("Failed to report message ID $messageId: " . $e->getMessage());
            return false;
        }
    }

    // Delete a message by ID
    public function deleteMessage($messageId)
    {
        if (!InputValidator::isValidInteger($messageId)) {
            Logger::error("Invalid message ID: $messageId");
            return false;
        }

        $sql = "DELETE FROM messages WHERE id = :messageId";
        $stmt = $this->pdo->prepare($sql);

        try {
            return $stmt->execute([':messageId' => (int)$messageId]);
        } catch (PDOException $e) {
            Logger::error("Failed to delete message ID $messageId: " . $e->getMessage());
            return false;
        }
    }

    public function getPublicMessages()
    {
        try {
            $sql = "SELECT id AS message_id, content, created_at FROM messages";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            Logger::error("Error fetching public messages: " . $e->getMessage());
            return [];
        }
    }

}
