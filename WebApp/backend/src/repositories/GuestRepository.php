<?php

namespace repositories;

use helpers\InputValidator;
use helpers\Logger;
use managers\DatabaseManager;
use models\Comment;
use models\Course;
use models\Lecturer;

/**
 * Repository class for handling comment-related database operations.
 */
class CommentRepository
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

        $sql = "INSERT INTO comments (message_id, guest_name, content, created_at)
                VALUES (:message_id, :guest_name, :content, NOW())";

        $stmt = $this->db->prepareStmt($sql);
        $this->db->bindArrayToSqlStmt(
            $stmt,
            [':message_id', ':guest_name', ':content'],
            [(int)$messageId, InputValidator::sanitizeString($guestName), InputValidator::sanitizeString($content)]
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

        $sql = "SELECT id, message_id, guest_name, content, created_at 
                FROM comments 
                WHERE message_id = :message_id 
                ORDER BY created_at ASC";

        $stmt = $this->db->prepareStmt($sql);
        $this->db->bindSingleValueToSqlStmt($stmt, ':message_id', (int)$messageId);
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

        $sql = "UPDATE messages SET is_reported = 1 WHERE id = :messageId";
        $stmt = $this->db->prepareStmt($sql);
        $this->db->bindSingleValueToSqlStmt($stmt, ':messageId', $messageId);

        $loggerMessage = "Reporting message ID: $messageId";
        return $this->db->executeTransaction($stmt, $loggerMessage);
    }

    public function getLecturerById (int $lecturerId): ?Lecturer
    {
        if (!InputValidator::isValidInteger($lecturerId)) {
            Logger::error("Invalid lecturer ID: $lecturerId");
            return null;
        }

        $sql = "SELECT name, image_path FROM users WHERE id = :lecturer_id" AND " role = 'lecturer'";
        $stmt = $this->db->prepareStmt($sql);
        $this->db->bindSingleValueToSqlStmt($stmt, ':lecturerId', (int)$lecturerId);

        $loggerMessage = "Fetching lecturer ID: $lecturerId";
        return $this->db->fetchSingle($stmt, $loggerMessage);
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

        $sql = "SELECT id, code, name, pin_code, lecturer_id FROM courses WHERE pin_code = :pin_code";
        $stmt = $this->db->prepareStmt($sql);
        //$this->db->bindSingleValueToSqlStmt($stmt, ":id", $courseId);

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
        $sql = "SELECT m.id AS message_id, m.content, m.reply, m.created_at, m.anonymous_id
                FROM messages m WHERE m.course_id = :courseId";

        $stmt = $this->db->prepareStmt($sql);
        $this->db->bindSingleValueToSqlStmt($stmt, ':courseId', $courseId);

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
