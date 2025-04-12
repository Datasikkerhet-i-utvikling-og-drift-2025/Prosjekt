<?php

namespace repositories;

use helpers\InputValidator;
use helpers\Logger;
use managers\DatabaseManager;
use models\Comment;
use models\Course;
use models\Lecturer;
use PDO;

/**
 * Repository class for handling comment-related database operations.
 */
class GuestRepository
{
    private DatabaseManager $db;

    /**
     * CommentRepository constructor.
     *
     * @param DatabaseManager $db Database service instance for handling queries.
     */
    public function __construct(DatabaseManager $db)
    {
        $this->db = $db;
    }

    /**
     * Adds a new comment to the database.
     *
     * @param int $messageId The ID of the associated message.
     * @param string $guestName The name of the commenter.
     * @param string $content The content of the comment.
     *
     * @return bool Returns true if the comment was successfully added, false otherwise.
     */
    public function addComment(int $messageId, string $guestName, string $content): bool
    {
        if (!InputValidator::isValidInteger($messageId)) {
            Logger::error("Invalid message ID: $messageId");
            return false;
        }

        if (!InputValidator::isNotEmpty($content)) {
            Logger::error("Failed to add comment: Content is empty");
            return false;
        }

        $sql = "CALL addComment(:message_id, :guest_name, :content)";

        $stmt = $this->db->prepareStmt($sql, fn($stmt) => $stmt
            ->bindValue(":message_id", $messageId, PDO::PARAM_STR)
            ->bindValue(":guest_name", InputValidator::sanitizeString($guestName), PDO::PARAM_STR)
            ->bindValue(":content", InputValidator::sanitizeString($content), PDO::PARAM_STR)
        );

        $logger = "Adding comment to message ID: $messageId";
        return $this->db->executeTransaction($stmt, $logger);
    }

    /**
     * Retrieves all comments associated with a specific message.
     *
     * @param int $messageId The ID of the message.
     *
     * @return Comment[] Returns an array of Comment objects.
     */
    public function getCommentsByMessageId(int $messageId): array
    {
        if (!InputValidator::isValidInteger($messageId)) {
            Logger::error("Invalid message ID: $messageId");
            return [];
        }

        $sql = "CALL getCommentsByMessageId(:message_id)";

        $stmt = $this->db->prepareStmt($sql, fn($stmt) => $stmt
            ->bindValue(":message_id", (int)$messageId, PDO::PARAM_INT)
        );
        $logger = "Fetching comments for message ID: $messageId";

        $commentsData = $this->db->fetchAll($stmt, $logger);
        $comments = [];

        foreach ($commentsData as $commentData) {
            $comments[] = new Comment($commentData);
        }

        return $comments;
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

        $sql = "CALL reportMessage(:messageId, :reason)";
        $stmt = $this->db->prepareStmt($sql, fn($stmt) => $stmt
            ->bindValue(":messageId", $messageId, PDO::PARAM_INT)
            ->bindValue(":reason", InputValidator::sanitizeString($reason), PDO::PARAM_STR)
        );

        $loggerMessage = "Reporting message ID: $messageId";
        return $this->db->executeTransaction($stmt, $loggerMessage);
    }

    public function getLecturerById(int $lecturerId): ?Lecturer
    {
        if (!InputValidator::isValidInteger($lecturerId)) {
            Logger::error("Invalid lecturer ID: $lecturerId");
            return null;
        }

        $sql = "CALL getLecturerById(:lecturerId)";
        $stmt = $this->db->prepareStmt($sql, fn($stmt) => $stmt
            ->bindValue(":lecturerId", $lecturerId, PDO::PARAM_INT)
        );

        $loggerMessage = "Fetching lecturer ID: $lecturerId";
        $lecturerData = $this->db->fetchSingle($stmt, $loggerMessage);

        if ($lecturerData) {
            return new Lecturer($lecturerData);
        }

        return null;
    }

    /**
     * Retrieves a course by its pinCode for guests.
     *
     * @param int $pinCode The pinCode of the course.
     *
     * @return Course|null Returns a Course object if found, otherwise null.
     */
    public function getCourseByPinCode(int $pinCode): ?Course
    {
        if (!InputValidator::isValidInteger($pinCode)) {
            Logger::error("Invalid course pin: $pinCode");
            return null;
        }

        $sql = "CALL getCourseByPinCode(:pinCode)";
        $stmt = $this->db->prepareStmt($sql, fn($stmt) => $stmt
            ->bindValue(":pinCode", $pinCode, PDO::PARAM_INT)
        );

        $logger = "Fetching course by pinCode: " . $pinCode;
        $data = $this->db->fetchSingle($stmt, $logger);

        return $data ? new Course($data) : null;
    }

    /**
     * Retrieves all messages for a specific course.
     *
     * @param int $courseId The ID of the course.
     * @return array Returns an array of messages.
     */
    public function getMessagesByCourse(int $courseId): array
    {
        $sql = "CALL getMessagesForCourse(:courseId)";

        $stmt = $this->db->prepareStmt($sql, fn($stmt) => $stmt
            ->bindValue(":courseId", $courseId, PDO::PARAM_INT)
        );

        $loggerMessage = "Fetching messages for course ID: $courseId";
        return $this->db->fetchAll($stmt, $loggerMessage);
    }

    /**
     * Deletes a comment from the database by its ID.
     *
     * @param int $commentId The ID of the comment to delete.
     *
     * @return bool Returns true if the deletion was successful, false otherwise.
     */
    /*public function deleteComment(int $commentId): bool
    {
        if (!InputValidator::isValidInteger($commentId)) {
            Logger::error("Invalid comment ID: $commentId");
            return false;
        }

        $sql = "DELETE FROM comments 
                WHERE id = :id";

        $stmt = $this->db->prepareStmt($sql);
        $this->db->bindSingleValueToSqlStmt($stmt, ':id', (int)$commentId);
        $logger = "Deleting comment ID: $commentId";

        return $this->db->executeTransaction($stmt, $logger);
    }*/
}
