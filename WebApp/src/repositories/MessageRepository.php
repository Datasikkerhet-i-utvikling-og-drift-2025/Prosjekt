<?php

namespace repositories;

use models\Message;
use service\DatabaseService;
use helpers\InputValidator;
use helpers\Logger;

use DateMalformedStringException;
use PDO;
use PDOException;

class MessageRepository
{
    private DatabaseService $db;

    /**
     * Constructs a new MessageRepository instance.
     *
     * @param DatabaseService $db The database service used for queries.
     */
    public function __construct(DatabaseService $db)
    {
        $this->db = $db;
    }


    /**
     * Creates a new message in the database.
     *
     * @param int $studentId The ID of the student sending the message.
     * @param int $courseId The ID of the associated course.
     * @param string $anonymousId The unique identifier for anonymous messages.
     * @param string $content The content of the message.
     * @return bool Returns true if the message was successfully inserted, false otherwise.
     */
    public function createMessage(int $studentId, int $courseId, string $anonymousId, string $content): bool
    {
        if (!InputValidator::isNotEmpty($content)) {
            Logger::error("Message content is empty for student ID $studentId and course ID $courseId");
            return false;
        }

        $sql = "INSERT INTO messages (student_id, course_id, anonymous_id, content, created_at, is_reported)
                VALUES (:studentId, :courseId, :anonymousId, :content, NOW(), 0)";
        $params = [':studentId', ':courseId', ':anonymousId', ':content'];
        $values = [
            $studentId,
            $courseId,
            InputValidator::sanitizeString($anonymousId),
            InputValidator::sanitizeString($content)
        ];

        $stmt = $this->db->prepareSql($sql);
        $this->db->bindArrayToSqlStmt($stmt, $params, $values);

        $loggerMessage = "Creating a new message for student ID: $studentId in course ID: $courseId";
        return $this->db->executeSql($stmt, $loggerMessage);
    }


    /**
     * Retrieves all messages for a specific course.
     *
     * @param int $courseId The ID of the course.
     * @return array Returns an array of messages.
     */
    public function getMessagesByCourse(int $courseId): array
    {
        $sql = "SELECT m.id AS message_id, m.content, m.reply, m.created_at, m.anonymous_id
                FROM messages m WHERE m.course_id = :courseId";

        $stmt = $this->db->prepareSql($sql);
        $this->db->bindSingleValueToSqlStmt($stmt, ':courseId', $courseId);

        $loggerMessage = "Fetching messages for course ID: $courseId";
        return $this->db->fetchAll($stmt, $loggerMessage);
    }


    /**
     * Retrieves all messages sent by a specific student.
     *
     * @param int $studentId The ID of the student.
     * @return array Returns an array of messages.
     */
    public function getMessagesByStudent(int $studentId): array
    {
        $sql = "SELECT m.id AS message_id, m.content, m.reply, m.created_at, 
                       c.code AS course_code, c.name AS course_name
                FROM messages m
                JOIN courses c ON m.course_id = c.id
                WHERE m.student_id = :studentId";

        $stmt = $this->db->prepareSql($sql);
        $this->db->bindSingleValueToSqlStmt($stmt, ':studentId', $studentId);

        $loggerMessage = "Fetching messages for student ID: $studentId";
        return $this->db->fetchAll($stmt, $loggerMessage);
    }


    /**
     * Retrieves a specific message by ID.
     *
     * @param int $messageId The ID of the message.
     * @return array|null Returns an associative array of message data or null if not found.
     */
    public function getMessageById(int $messageId): ?array
    {
        $sql = "SELECT m.id AS message_id, m.content, m.reply, m.created_at, 
                       c.code AS course_code, c.name AS course_name
                FROM messages m
                JOIN courses c ON m.course_id = c.id
                WHERE m.id = :messageId";

        $stmt = $this->db->prepareSql($sql);
        $this->db->bindSingleValueToSqlStmt($stmt, ':messageId', $messageId);

        $loggerMessage = "Fetching message ID: $messageId";
        return $this->db->fetchSingle($stmt, $loggerMessage);
    }


    /**
     * Updates the reply to a message.
     *
     * @param int $messageId The ID of the message.
     * @param string $replyContent The reply content.
     * @return bool Returns true if the update was successful, false otherwise.
     */
    public function updateMessageReply(int $messageId, string $replyContent): bool
    {
        if (!InputValidator::isNotEmpty($replyContent)) {
            Logger::error("Reply content is empty for message ID $messageId");
            return false;
        }

        $sql = "UPDATE messages SET reply = :replyContent, updated_at = NOW() WHERE id = :messageId";
        $stmt = $this->db->prepareSql($sql);
        $this->db->bindArrayToSqlStmt($stmt, [':messageId', ':replyContent'], [$messageId, InputValidator::sanitizeString($replyContent)]);

        $loggerMessage = "Updating reply for message ID: $messageId";
        return $this->db->executeSql($stmt, $loggerMessage);
    }


    /**
     * Reports a message as inappropriate.
     *
     * @param int $messageId The ID of the message.
     * @param string $reason The reason for reporting.
     * @return bool Returns true if the message was successfully reported, false otherwise.
     */
    public function reportMessageById(int $messageId, string $reason): bool
    {
        if (!InputValidator::isNotEmpty($reason)) {
            Logger::error("Report reason is empty for message ID $messageId");
            return false;
        }

        $sql = "UPDATE messages SET is_reported = 1 WHERE id = :messageId";
        $stmt = $this->db->prepareSql($sql);
        $this->db->bindSingleValueToSqlStmt($stmt, ':messageId', $messageId);

        $loggerMessage = "Reporting message ID: $messageId";
        return $this->db->executeSql($stmt, $loggerMessage);
    }


    /**
     * Deletes a message by ID.
     *
     * @param int $messageId The ID of the message.
     * @return bool Returns true if the deletion was successful, false otherwise.
     */
    public function deleteMessageById(int $messageId): bool
    {
        $sql = "DELETE FROM messages WHERE id = :messageId";
        $stmt = $this->db->prepareSql($sql);
        $this->db->bindSingleValueToSqlStmt($stmt, ':messageId', $messageId);

        $loggerMessage = "Deleting message ID: $messageId";
        return $this->db->executeSql($stmt, $loggerMessage);
    }


    /**
     * Retrieves all public messages.
     *
     * @return array Returns an array of public messages.
     */
    public function getPublicMessages(): array
    {
        $sql = "SELECT id AS message_id, content, created_at FROM messages";
        $stmt = $this->db->prepareSql($sql);

        $loggerMessage = "Fetching all public messages";
        return $this->db->fetchAll($stmt, $loggerMessage);
    }
}
