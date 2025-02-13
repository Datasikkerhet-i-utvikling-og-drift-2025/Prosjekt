<?php

namespace models;

use helpers\Logger;
use PDO;
use PDOException;
use PDOStatement;

require_once __DIR__ . '/../helpers/InputValidator.php';
require_once __DIR__ . '/../helpers/Logger.php';

class Admin extends User
{
    public function __construct(array $userData)
    {
        parent::__construct($userData);
    }

    /**
     * Binds the user's properties as parameters for a prepared PDO statement.
     *
     * This method ensures that all relevant user attributes are securely bound to a
     * prepared SQL statement before execution, reducing the risk of SQL injection.
     *
     * @param PDOStatement $stmt The prepared statement to which user attributes will be bound.
     *
     * @return void
     */
    public function bindUserDataForDbStmt(PDOStatement $stmt): void
    {
        parent::bindUserDataForDbStmt($stmt);
    }
}
    // Delete a user by ID
    public function deleteUserById($id)
    {
        try {
            return parent::deleteUser($id);
        } catch (PDOException $e) {
            Logger::error("Failed to delete user ID $id: " . $e->getMessage());
            return false;
        }
    }

    // Delete a message by ID
    public function deleteMessage($messageId)
    {
        $sql = "DELETE FROM messages WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        try {
            return $stmt->execute([':id' => $messageId]);
        } catch (PDOException $e) {
            Logger::error("Failed to delete message ID $messageId: " . $e->getMessage());
            return false;
        }
    }

    // Update a message's content (e.g., to censor something)
    public function updateMessage($messageId, $newContent)
    {
        if (!InputValidator::isNotEmpty($newContent)) {
            Logger::error("New content is empty for message ID $messageId");
            return false;
        }

        $sql = "UPDATE messages SET content = :content, updated_at = NOW() WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        try {
            return $stmt->execute([
                ':id' => $messageId,
                ':content' => InputValidator::sanitizeString($newContent),
            ]);
        } catch (PDOException $e) {
            Logger::error("Failed to update message ID $messageId: " . $e->getMessage());
            return false;
        }
    }

    // View all reported messages
    public function getReportedMessages()
    {
        $sql = "SELECT m.id AS message_id, m.content, r.report_reason, u.name AS reported_by, m.created_at
                FROM messages m
                LEFT JOIN reports r ON m.id = r.message_id
                LEFT JOIN users u ON r.reported_by = u.id";
        $stmt = $this->pdo->query($sql);

        try {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Logger::error("Failed to fetch reported messages: " . $e->getMessage());
            return [];
        }
    }

    // Get all users filtered by role
    public function getAllUsersByRole($role)
    {
        try {
            return parent::getAllUsers($role);
        } catch (PDOException $e) {
            Logger::error("Failed to fetch users by role $role: " . $e->getMessage());
            return [];
        }
    }

    // Find the sender of a specific message
    public function findMessageSender($messageId)
    {
        $sql = "SELECT m.id AS message_id, m.content, u.id AS sender_id, u.name, u.email, u.study_program, u.study_year
                FROM messages m
                JOIN users u ON m.student_id = u.id
                WHERE m.id = :id";
        $stmt = $this->pdo->prepare($sql);

        try {
            $stmt->execute([':id' => $messageId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Logger::error("Failed to find sender for message ID $messageId: " . $e->getMessage());
            return null;
        }
    }
}
