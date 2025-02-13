<?php

namespace repositories;

use helpers\Logger;
use helpers\InputValidator;
use service\DatabaseService;

use PDOException;

class LecturerRepository
{
    private DatabaseService $db;


    /**
     * Constructs a LecturerRepository instance.
     *
     * @param DatabaseService $db The database service instance for handling database operations.
     */
    public function __construct(DatabaseService $db)
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
        $sql = "SELECT id, code, name, pin_code, created_at FROM courses WHERE lecturer_id = :lecturerId";

        $stmt = $this->db->prepareSql($sql);
        $this->db->bindSingleValueToSqlStmt($stmt, ":lecturerId", $lecturerId);

        $logger = "Fetching courses for lecturer ID: $lecturerId";
        return $this->db->fetchAll($stmt, $logger);
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

        $stmt = $this->db->prepareSql($sql);
        $this->db->bindSingleValueToSqlStmt($stmt, ":courseId", $courseId);

        $logger = "Fetching messages for course ID: $courseId";
        return $this->db->fetchAll($stmt, $logger);
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
        // Validate reply content
        if (!InputValidator::isNotEmpty($replyContent)) {
            Logger::error("Reply content is empty for message ID: $messageId");
            return false;
        }

        $sql = "UPDATE messages SET reply = :replyContent, updated_at = NOW() WHERE id = :messageId";

        $stmt = $this->db->prepareSql($sql);
        $this->db->bindArrayToSqlStmt($stmt, [':messageId', ':replyContent'], [
            $messageId,
            InputValidator::sanitizeString($replyContent)
        ]);

        $logger = "Replying to message ID: $messageId";
        return $this->db->executeSql($stmt, $logger);
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

        $stmt = $this->db->prepareSql($sql);
        $this->db->bindSingleValueToSqlStmt($stmt, ":messageId", $messageId);

        $logger = "Fetching message ID: $messageId";
        return $this->db->fetchSingle($stmt, $logger);
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
        // Validate report reason
        if (!InputValidator::isNotEmpty($reason)) {
            Logger::error("Report reason is empty for message ID: $messageId");
            return false;
        }

        $sql = "INSERT INTO reports (message_id, report_reason, created_at)
                VALUES (:messageId, :reason, NOW())";

        $stmt = $this->db->prepareSql($sql);
        $this->db->bindArrayToSqlStmt($stmt, [':messageId', ':reason'], [
            $messageId,
            InputValidator::sanitizeString($reason)
        ]);

        $logger = "Reporting message ID: $messageId";
        return $this->db->executeSql($stmt, $logger);
    }
}
