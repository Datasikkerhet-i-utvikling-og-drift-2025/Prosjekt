<?php

namespace models;

use helpers\InputValidator;

use DateTime;
use DateMalformedStringException;
use PDO;
use PDOStatement;

class Message
{
    /** @var int|null $id Unique identifier for the message (auto-incremented in the database). */
    public ?int $id {
        get {
            return $this->id;
        }
        set {
            $this->id = $value;
        }
    }

    /** @var int $courseId ID of the course the message is related to. */
    public int $courseId {
        get {
            return $this->courseId;
        }
        set {
            $this->courseId = $value;
        }
    }

    /** @var int $studentId ID of the student who sent the message. */
    public int $studentId {
        get {
            return $this->studentId;
        }
        set {
            $this->studentId = $value;
        }
    }

    /** @var string $anonymousId A unique identifier for anonymous messages. */
    public string $anonymousId {
        get {
            return $this->anonymousId;
        }
        set {
            $this->anonymousId = $value;
        }
    }

    /** @var string $content The content of the message. */
    public string $content {
        get {
            return $this->content;
        }
        set {
            $this->content = $value;
        }
    }

    /** @var string|null $reply The lecturer's reply to the message, if any. */
    public ?string $reply {
        get {
            return $this->reply;
        }
        set {
            $this->reply = $value;
        }
    }

    /** @var DateTime $updatedAt Timestamp of the last modification of the message. */
    public DateTime $updatedAt {
        get {
            return $this->updatedAt;
        }
        set {
            $this->updatedAt = $value;
        }
    }

    /**
     * Constructs a new Message instance.
     *
     * This constructor initializes the message properties based on the provided message data array.
     * It ensures that timestamps are set correctly and assigns default values where necessary.
     *
     * @param array $messageData Associative array containing message data with the following keys:<br>
     *        - `id` (int|null) Message ID (auto-incremented in the database).<br>
     *        - `courseId` (int) ID of the associated course.<br>
     *        - `studentId` (int) ID of the student who sent the message.<br>
     *        - `anonymousId` (string) Unique identifier for anonymous messages.<br>
     *        - `content` (string) Message content.<br>
     *        - `reply` (string|null) Lecturer's reply (if applicable).<br>
     *        - `isReported` (bool) Whether the message is reported.<br>
     *        - `createdAt` (string|null) Timestamp when the message was created (defaults to `now`).<br>
     *        - `updatedAt` (string|null) Timestamp when the message was last updated (defaults to `now`).
     *
     * @throws DateMalformedStringException If any provided date string cannot be converted to a DateTime object.
     */
    public function __construct(array $messageData)
    {
        $this->id = $messageData['id'] ?? null;
        $this->courseId = $messageData['courseId'];
        $this->studentId = $messageData['studentId'];
        $this->anonymousId = InputValidator::sanitizeString($messageData['anonymousId']);
        $this->content = InputValidator::sanitizeString($messageData['content']);
        $this->reply = isset($messageData['reply']) ? InputValidator::sanitizeString($messageData['reply']) : null;
        $this->isReported = $messageData['isReported'] ?? false;
        $this->createdAt = new DateTime($messageData['createdAt'] ?? 'now');
        $this->updatedAt = new DateTime($messageData['updatedAt'] ?? 'now');
    }

    /**
     * Binds the message's properties as parameters for a prepared PDO statement.
     *
     * This method ensures that all relevant message attributes are securely bound to a
     * prepared SQL statement before execution, reducing the risk of SQL injection.
     *
     * @param PDOStatement $stmt The prepared statement to which message attributes will be bound.
     *
     * @return void
     */
    public function bindMessageDataForDbStmt(PDOStatement $stmt): void
    {
        $stmt->bindValue(':id', $this->id ?? null, $this->id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':courseId', $this->courseId, PDO::PARAM_INT);
        $stmt->bindValue(':studentId', $this->studentId, PDO::PARAM_INT);
        $stmt->bindValue(':anonymousId', $this->anonymousId, PDO::PARAM_STR);
        $stmt->bindValue(':content', $this->content, PDO::PARAM_STR);
        $stmt->bindValue(':reply', $this->reply ?? null, $this->reply !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':isReported', $this->isReported, PDO::PARAM_BOOL);
    }
}