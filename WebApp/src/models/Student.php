<?php

require_once 'User.php';

class Student extends User
{
    public function __construct($pdo)
    {
        parent::__construct($pdo);
    }

    // Send a message to a course
    public function sendMessage($studentId, $courseId, $anonymousId, $content)
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

    // View all messages the student has sent
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

    // View a specific message and its reply
    public function getMessageWithReply($messageId, $studentId)
    {
        $sql = "SELECT m.id AS message_id, m.content, m.reply, m.created_at, c.code AS course_code, c.name AS course_name
                FROM messages m
                JOIN courses c ON m.course_id = c.id
                WHERE m.id = :messageId AND m.student_id = :studentId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':messageId' => $messageId,
            ':studentId' => $studentId,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get a list of all courses available to the student
    public function getAvailableCourses()
    {
        $sql = "SELECT * FROM courses";
        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
