<?php

require_once 'User.php';
require_once __DIR__ . '/../helpers/InputValidator.php';
require_once __DIR__ . '/../helpers/Logger.php';

class Student extends User
{
    public function __construct($pdo)
    {
        parent::__construct($pdo);
    }

    // Send a message to a course
    public function sendMessage($studentId, $courseId, $anonymousId, $content)
    {
        // Validate inputs
        if (!InputValidator::isNotEmpty($content)) {
            Logger::error("Message content is empty for student ID: $studentId");
            return false;
        }

        $sql = "INSERT INTO messages (student_id, course_id, anonymous_id, content, created_at)
                VALUES (:studentId, :courseId, :anonymousId, :content, NOW())";
        $stmt = $this->pdo->prepare($sql);

        try {
            return $stmt->execute([
                ':studentId' => $studentId,
                ':courseId' => $courseId,
                ':anonymousId' => $anonymousId,
                ':content' => InputValidator::sanitizeString($content),
            ]);
        } catch (PDOException $e) {
            Logger::error("Failed to send message: " . $e->getMessage());
            return false;
        }
    }

    // View all messages the student has sent
    public function getMessagesByStudent($studentId)
    {
        $sql = "SELECT m.id AS message_id, m.content, m.reply, m.created_at, 
                       c.code AS course_code, c.name AS course_name
                FROM messages m
                JOIN courses c ON m.course_id = c.id
                WHERE m.student_id = :studentId
                ORDER BY m.created_at DESC"; // Added ORDER BY for consistent ordering
        $stmt = $this->pdo->prepare($sql);

        try {
            $stmt->execute([':studentId' => $studentId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Logger::error("Failed to fetch messages for student ID $studentId: " . $e->getMessage());
            return [];
        }
    }

    // View a specific message and its reply
    public function getMessageWithReply($messageId, $studentId)
    {
        $sql = "SELECT m.id AS message_id, m.content, m.reply, m.created_at, 
                       c.code AS course_code, c.name AS course_name
                FROM messages m
                JOIN courses c ON m.course_id = c.id
                WHERE m.id = :messageId AND m.student_id = :studentId";
        $stmt = $this->pdo->prepare($sql);

        try {
            $stmt->execute([
                ':messageId' => $messageId,
                ':studentId' => $studentId,
            ]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Logger::error("Failed to fetch message ID $messageId for student ID $studentId: " . $e->getMessage());
            return null;
        }
    }

    // Get a list of all courses available to the student
    public function getAvailableCourses()
    {
        $sql = "SELECT id, code, name FROM courses"; // Removed unnecessary pin_code
        try {
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Logger::error("Failed to fetch available courses: " . $e->getMessage());
            return [];
        }
    }
}
