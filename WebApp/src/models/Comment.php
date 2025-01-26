<?php

require_once __DIR__ . '/../helpers/InputValidator.php';
require_once __DIR__ . '/../helpers/Logger.php';

class Comment
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Add a comment to the database
    public function addComment($messageId, $guestName, $content)
    {
        // Validate inputs
        if (!InputValidator::isValidInteger($messageId)) {
            Logger::error("Invalid message ID: $messageId");
            return false;
        }

        if (!InputValidator::isNotEmpty($guestName)) {
            Logger::error("Failed to add comment: Guest name is empty");
            return false;
        }

        if (!InputValidator::isNotEmpty($content)) {
            Logger::error("Failed to add comment: Content is empty");
            return false;
        }

        $sql = "INSERT INTO comments (message_id, guest_name, content, created_at)
                VALUES (:message_id, :guest_name, :content, NOW())";
        $stmt = $this->pdo->prepare($sql);

        try {
            return $stmt->execute([
                ':message_id' => (int)$messageId,
                ':guest_name' => InputValidator::sanitizeString($guestName),
                ':content' => InputValidator::sanitizeString($content),
            ]);
        } catch (PDOException $e) {
            Logger::error("Failed to add comment for message ID $messageId: " . $e->getMessage());
            return false;
        }
    }

    // Get all comments for a specific message
    public function getCommentsByMessageId($messageId)
    {
        // Validate input
        if (!InputValidator::isValidInteger($messageId)) {
            Logger::error("Invalid message ID: $messageId");
            return [];
        }

        $sql = "SELECT id, message_id, guest_name, content, created_at 
                FROM comments 
                WHERE message_id = :message_id 
                ORDER BY created_at ASC";
        $stmt = $this->pdo->prepare($sql);

        try {
            $stmt->execute([':message_id' => (int)$messageId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Logger::error("Failed to fetch comments for message ID $messageId: " . $e->getMessage());
            return [];
        }
    }

    // Delete a comment by ID
    public function deleteComment($commentId)
    {
        // Validate input
        if (!InputValidator::isValidInteger($commentId)) {
            Logger::error("Invalid comment ID: $commentId");
            return false;
        }

        $sql = "DELETE FROM comments WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        try {
            return $stmt->execute([':id' => (int)$commentId]);
        } catch (PDOException $e) {
            Logger::error("Failed to delete comment ID $commentId: " . $e->getMessage());
            return false;
        }
    }
}
