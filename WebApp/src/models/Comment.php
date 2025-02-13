<?php

namespace models;

use helpers\InputValidator;

use DateTime;
use Exception;
use PDO;
use PDOStatement;

/**
 * Represents a comment made on a message.
 */
class Comment
{
    /** @var int|null $id Unique identifier for the comment (auto-incremented in the database). */
    public ?int $id {
        get {
            return $this->id;
        }
        set {
            $this->id = $value;
        }
    }

    /** @var int $messageId The ID of the message this comment is associated with. */
    public int $messageId {
        get {
            return $this->messageId;
        }
        set {
            $this->messageId = $value;
        }
    }

    /** @var string $guestName The name of the guest who posted the comment. */
    public string $guestName {
        get {
            return $this->guestName;
        }
        set {
            $this->guestName = $value;
        }
    }

    /** @var string $content The content of the comment. */
    public string $content {
        get {
            return $this->content;
        }
        set {
            $this->content = $value;
        }
    }

    /** @var DateTime $createdAt Timestamp when the comment was created. */
    public DateTime $createdAt {
        get {
            return $this->createdAt;
        }
        set {
            $this->createdAt = $value;
        }
    }


    /**
     * Constructs a new Comment instance.
     *
     * @param array $commentData Associative array containing comment data:
     *        - `id` (int|null) Unique ID (if null, assigned by database).
     *        - `messageId` (int) ID of the associated message.
     *        - `guestName` (string) Name of the commenter.
     *        - `content` (string) Comment text.
     *        - `createdAt` (string|null) Timestamp when the comment was created (defaults to `now`).
     *
     * @throws Exception If any provided date string cannot be converted to a DateTime object.
     */
    public function __construct(array $commentData)
    {
        $this->id = $commentData['id'] ?? null;
        $this->messageId = (int)$commentData['messageId'];
        $this->guestName = InputValidator::sanitizeString($commentData['guestName']);
        $this->content = InputValidator::sanitizeString($commentData['content']);
        $this->createdAt = new DateTime($commentData['createdAt'] ?? 'now');
    }


    /**
     * Binds the comment properties as parameters for a prepared PDO statement.
     *
     * This method ensures that all relevant comment attributes are securely bound to
     * a prepared SQL statement before execution, reducing the risk of SQL injection.
     *
     * @param PDOStatement $stmt The prepared statement to which comment attributes will be bound.
     *
     * @return void
     */
    public function bindCommentDataForDbStmt(PDOStatement $stmt): void
    {
        $stmt->bindValue(':id', $this->id ?? null, $this->id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':messageId', $this->messageId, PDO::PARAM_INT);
        $stmt->bindValue(':guestName', $this->guestName, PDO::PARAM_STR);
        $stmt->bindValue(':content', $this->content, PDO::PARAM_STR);
        $stmt->bindValue(':createdAt', $this->createdAt->format('Y-m-d H:i:s'), PDO::PARAM_STR);
    }
}