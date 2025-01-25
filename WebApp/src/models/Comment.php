<?php

class Comment {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Add a comment to the database
    public function addComment($messageId, $guestName, $content) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO comments (message_id, guest_name, content, created_at) VALUES (:message_id, :guest_name, :content, NOW())");
            $stmt->bindParam(':message_id', $messageId);
            $stmt->bindParam(':guest_name', $guestName);
            $stmt->bindParam(':content', $content);
            return $stmt->execute(); // Returns true if successful, false otherwise
        } catch (PDOException $e) {
            error_log("Failed to add comment: " . $e->getMessage());
            return false;
        }
    }

    // Get all comments for a specific message
    public function getCommentsByMessageId($messageId) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM comments WHERE message_id = :message_id ORDER BY created_at ASC");
            $stmt->bindParam(':message_id', $messageId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC); // Returns an array of comments
        } catch (PDOException $e) {
            error_log("Failed to fetch comments: " . $e->getMessage());
            return [];
        }
    }

    // Delete a comment by ID
    public function deleteComment($commentId) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM comments WHERE id = :id");
            $stmt->bindParam(':id', $commentId);
            return $stmt->execute(); // Returns true if successful, false otherwise
        } catch (PDOException $e) {
            error_log("Failed to delete comment: " . $e->getMessage());
            return false;
        }
    }
}
