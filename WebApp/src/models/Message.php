<?php

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
        $sql = "INSERT INTO messages (student_id, course_id, anonymous_id, content, created_at)
                VALUES (:studentId, :courseId, :anonymousId, :content, NOW())";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':studentId' => $studentId,
            ':courseId' => $courseId,
            ':anonymousId' => $anonymousId,
            ':content' => $content,
        ]);
    }

    // Retrieve all messages for a specific course
    public function getMessagesByCourse($courseId)
    {
        $sql = "SELECT m.id AS message_id, m.content, m.reply, m.created_at, u.anonymous_id
                FROM messages m
                LEFT JOIN users u ON m.student_id = u.id
                WHERE m.course_id = :courseId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':courseId' => $courseId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Retrieve all messages sent by a specific student
    public function getMessagesByStudent($studentId)
    {
        $sql = "SELECT m.id AS message_id, m.content, m.reply, m.created_at, c.code AS course_code, c.name AS course_name
                FROM messages m
                JOIN courses c ON m.course_id = c.id
                WHERE m.student_id = :studentId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':studentId' => $studentId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Retrieve a specific message by ID
    public function getMessageById($messageId)
    {
        $sql = "SELECT m.id AS message_id, m.content, m.reply, m.created_at, c.code AS course_code, c.name AS course_name
                FROM messages m
                JOIN courses c ON m.course_id = c.id
                WHERE m.id = :messageId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':messageId' => $messageId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update the reply to a message
    public function updateMessageReply($messageId, $replyContent)
    {
        $sql = "UPDATE messages SET reply = :replyContent, updated_at = NOW() WHERE id = :messageId";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':messageId' => $messageId,
            ':replyContent' => $replyContent,
        ]);
    }

    // Report a message as inappropriate
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

    // Delete a message by ID
    public function deleteMessage($messageId)
    {
        $sql = "DELETE FROM messages WHERE id = :messageId";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([':messageId' => $messageId]);
    }
}
