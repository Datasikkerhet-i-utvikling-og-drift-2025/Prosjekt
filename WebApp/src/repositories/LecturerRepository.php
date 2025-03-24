<?php

namespace repositories;

use helpers\InputValidator;
use helpers\Logger;
use managers\DatabaseManager;
use PDO;

class LecturerRepository
{
    private DatabaseManager $db;


    /**
     * Constructs a LecturerRepository instance.
     *
     * @param DatabaseManager $db The database service instance for handling database operations.
     */
    public function __construct(DatabaseManager $db)
    {
        $this->db = $db;
    }


    /**
     * Retrieves all courses taught by a lecturer.
     *
     * @param string $lecturerId The ID of the lecturer.
     *
     * @return array Returns an array of courses assigned to the lecturer.
     */
    public function getCourses(string $lecturerId): array
    {
        $sql = "SELECT id, code, name, pin_code, created_at 
                FROM courses 
                WHERE lecturer_id = :lecturerId";

        $this->db->prepareStmt(
            $sql,
            fn($stmt) => $stmt->bindValue(":lecturerId", $lecturerId, PDO::PARAM_STR)
        );

        return $this->db->fetchAll("Fetching courses for lecturer ID: $lecturerId");
    }


    /**
     * Retrieves all messages for a specific course.
     *
     * @param string $courseId The ID of the course.
     *
     * @return array Returns an array of messages associated with the course.
     */
    public function getMessagesForCourse(string $courseId): array
    {
        $sql = "SELECT m.id AS message_id, m.content, m.reply, m.created_at, m.anonymous_id
                FROM messages m
                WHERE m.course_id = :courseId";

        $this->db->prepareStmt(
            $sql,
            fn($stmt) => $stmt->bindValue(":courseId", $courseId, PDO::PARAM_STR)
        );

        return $this->db->fetchAll("Fetching messages for course ID: $courseId");
    }


    /**
     * Replies to a student's message.
     *
     * @param string $messageId The ID of the message being replied to.
     * @param string $replyContent The reply content.
     *
     * @return bool Returns true if the reply was successfully stored, false otherwise.
     */
    public function replyToMessage(string $messageId, string $replyContent): bool
    {
        if (!InputValidator::isNotEmpty($replyContent)) {
            Logger::error("Reply content is empty for message ID: $messageId");
            return false;
        }

        $sql = "UPDATE messages 
                SET reply = :replyContent, updated_at = NOW() 
                WHERE id = :messageId";

        $this->db->prepareStmt(
            $sql,
            fn($stmt) => $stmt
                ->bindValue(":messageId", $messageId, PDO::PARAM_STR)
                ->bindValue(":replyContent", InputValidator::sanitizeString($replyContent), PDO::PARAM_STR)
        );

        return $this->db->executeTransaction("Replying to message ID: $messageId");
    }


    /**
     * Retrieves a specific message by its ID.
     *
     * @param string $messageId The ID of the message.
     *
     * @return array|null Returns an associative array containing the message details or null if not found.
     */
    public function getMessageById(string $messageId): ?array
    {
        $sql = "SELECT m.id AS message_id, m.content, m.reply, m.created_at, m.anonymous_id
                FROM messages m
                WHERE m.id = :messageId";

        $this->db->prepareStmt(
            $sql,
            fn($stmt) => $stmt->bindValue(":messageId", $messageId, PDO::PARAM_STR)
        );

        return $this->db->fetchSingle("Fetching message ID: $messageId");
    }


    /**
     * Reports an inappropriate message.
     *
     * @param string $messageId The ID of the message being reported.
     * @param string $reason The reason for reporting.
     *
     * @return bool Returns true if the report was successfully submitted, false otherwise.
     */
    public function reportMessage(string $messageId, string $reason): bool
    {
        if (!InputValidator::isNotEmpty($reason)) {
            Logger::error("Report reason is empty for message ID: $messageId");
            return false;
        }

        $sql = "INSERT INTO reports (message_id, report_reason, created_at)
                VALUES (:messageId, :reason, NOW())";

        $this->db->prepareStmt(
            $sql,
            fn($stmt) => $stmt
                ->bindValue(":messageId", $messageId, PDO::PARAM_STR)
                ->bindValue(":reason", InputValidator::sanitizeString($reason), PDO::PARAM_STR)
        );

        return $this->db->executeTransaction("Reporting message ID: $messageId");
    }
}
