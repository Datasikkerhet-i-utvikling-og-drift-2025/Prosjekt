<?php

namespace repositories;

use helpers\InputValidator;
use helpers\Logger;
use managers\DatabaseManager;
use PDO;

class StudentRepository
{
    private DatabaseManager $db;


    /**
     * Constructs a StudentRepository instance.
     *
     * @param DatabaseManager $db The database service instance for handling database operations.
     */
    public function __construct(DatabaseManager $db)
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
        if (!InputValidator::isNotEmpty($content)) {
            Logger::error("Message content is empty for student ID: $studentId");
            return false;
        }

        $sql = "CALL sendMessage(:studentId, :courseId, :anonymousId, :content)";

        $this->db->prepareStmt(
            $sql,
            fn($stmt) => $stmt
                ->bindValue(":studentId", $studentId, PDO::PARAM_STR)
                ->bindValue(":courseId", $courseId, PDO::PARAM_STR)
                ->bindValue(":anonymousId", $anonymousId, PDO::PARAM_STR)
                ->bindValue(":content", InputValidator::sanitizeString($content), PDO::PARAM_STR)
        );

        return $this->db->executeTransaction("Sending message from student ID: $studentId to course ID: $courseId");
    }


    /**
     * Retrieves all messages sent by a specific student.
     *
     * @param string $studentId The ID of the student.
     *
     * @return array Returns an array of messages associated with the student.
     */
    public function getMessagesByStudent(int $studentId): array
    {
        $sql = "CALL getMessagesByStudent(:studentId)";

        $this->db->prepareStmt(
            $sql,
            fn($stmt) => $stmt->bindValue(":studentId", $studentId, PDO::PARAM_STR)
        );

        return $this->db->fetchAll("Fetching messages for student ID: $studentId");
    }


    /**
     * Retrieves a specific message along with its reply.
     *
     * @param string $messageId The ID of the message.
     * @param string $studentId The ID of the student who sent the message.
     *
     * @return array|null Returns an associative array containing the message details or null if not found.
     */
    public function getMessageWithReply(int $messageId, int $studentId): ?array
    {
        $sql = "CALL getMessageWithReply(:messageId, :studentId)";

        $this->db->prepareStmt(
            $sql,
            fn($stmt) => $stmt
                ->bindValue(":messageId", $messageId, PDO::PARAM_STR)
                ->bindValue(":studentId", $studentId, PDO::PARAM_STR)
        );

        return $this->db->fetchSingle("Fetching message ID: $messageId for student ID: $studentId");
    }


    /**
     * Retrieves a list of all available courses.
     *
     * @return array Returns an array of available courses.
     */
    public function getAvailableCourses(): array
    {
        $sql = "CALL getAvailableCourses()";

        $this->db->prepareStmt($sql);

        return $this->db->fetchAll("Fetching available courses");
    }
}
