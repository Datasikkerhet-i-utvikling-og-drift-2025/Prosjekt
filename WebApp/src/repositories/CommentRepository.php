<?php

namespace repositories;

use helpers\InputValidator;
use helpers\Logger;
use managers\DatabaseManager;
use models\Comment;

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
        return $this->db->executeStmt($stmt, $logger);
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
     * Deletes a comment from the database by its ID.
     *
     * @param int $commentId The ID of the comment to delete.
     *
     * @return bool Returns true if the deletion was successful, false otherwise.
     */
    public function deleteComment(int $commentId): bool
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

        return $this->db->executeStmt($stmt, $logger);
    }
}
