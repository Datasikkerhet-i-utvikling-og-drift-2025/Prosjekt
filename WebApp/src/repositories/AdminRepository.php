<?php

namespace repositories;

use helpers\InputValidator;
use helpers\Logger;
use managers\DatabaseManager;

class AdminRepository
{
    private DatabaseManager $db;


    /**
     * Constructs an AdminRepository instance.
     *
     * @param DatabaseManager $db The database service instance for handling database operations.
     */
    public function __construct(DatabaseManager $db)
    {
        $this->db = $db;
    }


    /**
     * Deletes a user by ID.
     *
     * @param string $id The ID of the user to delete.
     *
     * @return bool Returns true if the user was successfully deleted, false otherwise.
     */
    public function deleteUserById(string $id): bool
    {
        $sql = "DELETE FROM users WHERE id = :id";

        $stmt = $this->db->prepareStmt($sql);
        $this->db->bindSingleValueToSqlStmt($stmt, ":id", $id);

        $logger = "Deleting user ID: $id";
        return $this->db->executeStmt($stmt, $logger);
    }


    /**
     * Deletes a message by ID.
     *
     * @param string $messageId The ID of the message to delete.
     *
     * @return bool Returns true if the message was successfully deleted, false otherwise.
     */
    public function deleteMessage(string $messageId): bool
    {
        $sql = "DELETE FROM messages WHERE id = :id";

        $stmt = $this->db->prepareStmt($sql);
        $this->db->bindSingleValueToSqlStmt($stmt, ":id", $messageId);

        $logger = "Deleting message ID: $messageId";
        return $this->db->executeStmt($stmt, $logger);
    }


    /**
     * Updates the content of a message (e.g., to censor inappropriate content).
     *
     * @param string $messageId The ID of the message to update.
     * @param string $newContent The new content of the message.
     *
     * @return bool Returns true if the message was successfully updated, false otherwise.
     */
    public function updateMessage(string $messageId, string $newContent): bool
    {
        // Validate new content
        if (!InputValidator::isNotEmpty($newContent)) {
            Logger::error("New content is empty for message ID: $messageId");
            return false;
        }

        $sql = "UPDATE messages SET content = :content, updated_at = NOW() WHERE id = :id";

        $stmt = $this->db->prepareStmt($sql);
        $this->db->bindArrayToSqlStmt($stmt, [':id', ':content'], [
            $messageId,
            InputValidator::sanitizeString($newContent)
        ]);

        $logger = "Updating message ID: $messageId";
        return $this->db->executeStmt($stmt, $logger);
    }


    /**
     * Retrieves all reported messages.
     *
     * @return array Returns an array of reported messages.
     */
    public function getReportedMessages(): array
    {
        $sql = "SELECT m.id AS message_id, m.content, r.report_reason, u.name AS reported_by, m.created_at
                FROM messages m
                LEFT JOIN reports r ON m.id = r.message_id
                LEFT JOIN users u ON r.reported_by = u.id";

        $stmt = $this->db->prepareStmt($sql);
        $logger = "Fetching all reported messages";

        return $this->db->fetchAll($stmt, $logger);
    }


    /**
     * Retrieves all users filtered by role.
     *
     * @param string $role The role to filter users by (e.g., 'student', 'lecturer', 'admin').
     *
     * @return array Returns an array of users matching the specified role.
     */
    public function getAllUsersByRole(string $role): array
    {
        $sql = "SELECT * FROM users WHERE role = :role";

        $stmt = $this->db->prepareStmt($sql);
        $this->db->bindSingleValueToSqlStmt($stmt, ":role", $role);

        $logger = "Fetching users with role: $role";
        return $this->db->fetchAll($stmt, $logger);
    }


    /**
     * Finds the sender of a specific message.
     *
     * @param string $messageId The ID of the message.
     *
     * @return array|null Returns an associative array containing sender details or null if not found.
     */
    public function findMessageSender(string $messageId): ?array
    {
        $sql = "SELECT m.id AS message_id, m.content, u.id AS sender_id, u.name, u.email, u.study_program, u.study_year
                FROM messages m
                JOIN users u ON m.student_id = u.id
                WHERE m.id = :id";

        $stmt = $this->db->prepareStmt($sql);
        $this->db->bindSingleValueToSqlStmt($stmt, ":id", $messageId);

        $logger = "Finding sender for message ID: $messageId";
        return $this->db->fetchSingle($stmt, $logger);
    }
}
