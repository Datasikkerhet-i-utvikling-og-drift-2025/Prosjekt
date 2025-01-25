<?php

require_once 'User.php';

class Admin extends User
{
    // Constructor to inherit PDO connection from User
    public function __construct($pdo)
    {
        parent::__construct($pdo);
    }

    // Delete a user by ID
    public function deleteUserById($id)
    {
        return parent::deleteUser($id);
    }

    // Manage messages: Delete a message by ID
    public function deleteMessage($messageId)
    {
        $sql = "DELETE FROM messages WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([':id' => $messageId]);
    }

    // Manage messages: Update a message content (e.g., to censor something)
    public function updateMessage($messageId, $newContent)
    {
        $sql = "UPDATE messages SET content = :content, updated_at = NOW() WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':id' => $messageId,
            ':content' => $newContent,
        ]);
    }

    // View all reported messages
    public function getReportedMessages()
    {
        $sql = "SELECT m.id AS message_id, m.content, m.is_reported, r.report_reason, u.name AS reported_by
                FROM messages m
                LEFT JOIN reports r ON m.id = r.message_id
                LEFT JOIN users u ON r.reported_by = u.id
                WHERE m.is_reported = TRUE";
        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Manage users: Get all users with filtering by role
    public function getAllUsersByRole($role)
    {
        return parent::getAllUsers($role);
    }

    // Find out who sent a specific message (breaking anonymity)
    public function findMessageSender($messageId)
    {
        $sql = "SELECT m.id AS message_id, m.content, u.id AS sender_id, u.name, u.email
                FROM messages m
                JOIN users u ON m.student_id = u.id
                WHERE m.id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $messageId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
