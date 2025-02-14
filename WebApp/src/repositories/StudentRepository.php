<?php

namespace repositories;

use helpers\Logger;
use helpers\InputValidator;
use services\DatabaseService;

use PDOException;

class StudentRepository
{
    private DatabaseService $db;


    /**
     * Constructs a StudentRepository instance.
     *
     * @param DatabaseService $db The database service instance for handling database operations.
     */
    public function __construct(DatabaseService $db)
    {
        $this->db = $db;
    }


    /**
     * Sends an anonymous message from a student to a course.
     *
     * @param string $studentId The ID of the student sending the message.
     * @param string $courseId The ID of the course the message is sent to.
     * @param string|null $anonymousId The anonymous ID assigned to the student (if applicable).
     * @param string $content The content of the message.
     *
     * @return bool Returns true if the message was successfully sent, false otherwise.
     */
    public function sendMessage(string $studentId, string $courseId, ?string $anonymousId, string $content): bool
    {
        // Validate message content
        if (!InputValidator::isNotEmpty($content)) {
            Logger::error("Message content is empty for student ID: $studentId");
            return false;
        }

        $sql = "INSERT INTO messages (student_id, course_id, anonymous_id, content, created_at)
                VALUES (:studentId, :courseId, :anonymousId, :content, NOW())";

        $stmt = $this->db->prepareSql($sql);
        $this->db->bindArrayToSqlStmt($stmt, [':studentId', ':courseId', ':anonymousId', ':content'], [
            $studentId,
            $courseId,
            $anonymousId,
            InputValidator::sanitizeString($content)
        ]);

        $logger = "Sending message from student ID: $studentId to course ID: $courseId";
        return $this->db->executeSql($stmt, $logger);
    }


    /**
     * Retrieves all messages sent by a specific student.
     *
     * @param string $studentId The ID of the student.
     *
     * @return array Returns an array of messages associated with the student.
     */
    public function getMessagesByStudent(string $studentId): array
    {
        $sql = "SELECT m.id AS message_id, m.content, m.reply, m.created_at, 
                       c.code AS course_code, c.name AS course_name
                FROM messages m
                JOIN courses c ON m.course_id = c.id
                WHERE m.student_id = :studentId
                ORDER BY m.created_at DESC";

        $stmt = $this->db->prepareSql($sql);
        $this->db->bindSingleValueToSqlStmt($stmt, ":studentId", $studentId);

        $logger = "Fetching messages for student ID: $studentId";
        return $this->db->fetchAll($stmt, $logger);
    }


    /**
     * Retrieves a specific message along with its reply.
     *
     * @param string $messageId The ID of the message.
     * @param string $studentId The ID of the student who sent the message.
     *
     * @return array|null Returns an associative array containing the message details or null if not found.
     */
    public function getMessageWithReply(string $messageId, string $studentId): ?array
    {
        $sql = "SELECT m.id AS message_id, m.content, m.reply, m.created_at, 
                       c.code AS course_code, c.name AS course_name
                FROM messages m
                JOIN courses c ON m.course_id = c.id
                WHERE m.id = :messageId AND m.student_id = :studentId";

        $stmt = $this->db->prepareSql($sql);
        $this->db->bindArrayToSqlStmt($stmt, [':messageId', ':studentId'], [$messageId, $studentId]);

        $logger = "Fetching message ID: $messageId for student ID: $studentId";
        return $this->db->fetchSingle($stmt, $logger);
    }


    /**
     * Retrieves a list of all available courses.
     *
     * @return array Returns an array of available courses.
     */
    public function getAvailableCourses(): array
    {
        $sql = "SELECT id, code, name FROM courses";

        $stmt = $this->db->prepareSql($sql);
        $logger = "Fetching available courses";

        return $this->db->fetchAll($stmt, $logger);
    }
}
