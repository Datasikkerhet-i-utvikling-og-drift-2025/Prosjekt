<?php

namespace models;

use DateTime;
use helpers\InputValidator;
use helpers\Logger;

use InvalidArgumentException;
use PDO;
use PDOStatement;

/**
 * Represents a course in the system.
 */
class Course
{
    /** @var int|null $id Unique identifier for the course (auto-incremented in the database). */
    private ?int $id {
        get {
            return $this->id;
        }
    }

    /** @var string $code Unique course code (e.g., "CS101"). */
    private string $code {
        get {
            return $this->code;
        }
        set {
            $this->code = InputValidator::sanitizeString($value);
        }
    }

    /** @var string $name Name of the course. */
    private string $name {
        get {
            return $this->name;
        }
        set {
            $this->name = InputValidator::sanitizeString($value);
        }
    }

    /** @var int $lecturerId ID of the lecturer responsible for the course. */
    private int $lecturerId {
        get {
            return $this->lecturerId;
        }
        set {
            $this->lecturerId = $value;
        }
    }

    /** @var string $pinCode 4-digit code for course access. */
    private string $pinCode {
        get {
            return $this->pinCode;
        }
        set(string $newPinCode) {
            if (!preg_match('/^\d{4}$/', $newPinCode)) {
                Logger::error("Invalid PIN code: Must be exactly 4 digits.");
                throw new InvalidArgumentException("PIN code must be exactly 4 digits.");
            }
            $this->pinCode = $newPinCode;
        }
    }

    /** @var DateTime $createdAt Timestamp when the course was created. */
    private DateTime $createdAt {
        get {
            return $this->createdAt;
        }
        set {
            $this->createdAt = $value;
        }
    }

    /**
     * Constructs a new Course instance.
     *
     * @param array $courseData Associative array containing course data:
     *   - `id` (int|null) Course ID.
     *   - `code` (string) Unique course code.
     *   - `name` (string) Course name.
     *   - `lecturerId` (int) ID of the lecturer.
     *   - `pinCode` (string) 4-digit course access code.
     *   - `createdAt` (string|null) Course creation timestamp (defaults to 'now').
     * @throws \DateMalformedStringException
     */
    public function __construct(array $courseData)
    {
        $this->id = $courseData['id'] ?? null;
        $this->code = InputValidator::sanitizeString($courseData['courseCode']);
        $this->name = InputValidator::sanitizeString($courseData['courseName']);
        $this->lecturerId = (int) $courseData['lecturerId'];
        $this->pinCode = InputValidator::sanitizeString($courseData['pinCode']);
        $this->createdAt = new DateTime($courseData['createdAt'] ?? 'now');
    }


    /**
     * Sets the course PIN code.
     *
     * @param string $pinCode The 4-digit PIN code.
     */
    public function setPinCode(string $pinCode): void
    {
        if (!preg_match('/^\d{4}$/', $pinCode)) {
            Logger::error("Invalid PIN code: Must be exactly 4 digits.");
            throw new \InvalidArgumentException("PIN code must be exactly 4 digits.");
        }
        $this->pinCode = $pinCode;
    }


    /**
     * Binds the course properties as parameters for a prepared PDO statement.
     *
     * @param PDOStatement $stmt The prepared statement to bind data to.
     */
    public function bindCourseDataForDbStmt(PDOStatement $stmt): void
    {
        $stmt->bindValue(':code', $this->code, PDO::PARAM_STR);
        $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
        $stmt->bindValue(':lecturerId', $this->lecturerId, PDO::PARAM_INT);
        $stmt->bindValue(':pinCode', $this->pinCode, PDO::PARAM_STR);
    }

}