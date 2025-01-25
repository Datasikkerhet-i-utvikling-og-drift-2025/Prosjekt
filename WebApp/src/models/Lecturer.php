<?php

require_once 'User.php';

class Lecturer extends User
{
    public function __construct($pdo)
    {
        parent::__construct($pdo);
    }

    // Get all courses taught by the lecturer
    public function getCourses($lecturerId)
    {
        $sql = "SELECT * FROM courses WHERE lecturer_id = :lecturerId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':lecturerId' => $lecturerId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all messages for a specific course
    public function getMessagesForCourse($courseId)
    {
        $sql = "SELECT m.id AS message_id, m.content, m.reply, m.created_at, m.anonymous_id
                FROM messages m
                LEFT JOIN users u ON m.student_id = u.id
                WHERE m.course_id = :courseId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':courseId' => $courseId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Reply to a student's message
    public function replyToMessage($messageId, $replyContent)
    {
        $sql = "UPDATE messages SET reply = :replyContent, updated_at = NOW() WHERE id = :messageId";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':messageId' => $messageId,
            ':replyContent' => $replyContent,
        ]);
    }

    // View a specific message by ID (for detailed inspection)
    public function getMessageById($messageId)
    {
        $sql = "SELECT m.id AS message_id, m.content, m.reply, m.created_at, m.anonymous_id
                FROM messages m
                LEFT JOIN users u ON m.student_id = u.id
                WHERE m.id = :messageId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':messageId' => $messageId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Report an inappropriate message
    public function reportMessage($messageId, $reason)
    {
        $sql = "INSERT INTO reports (message_id, report_reason, created_at)
                VALUES (:messageId, :reason, NOW())";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':messageId' => $messageId,
            ':reason' => $reason,
        ]);
    }
}
