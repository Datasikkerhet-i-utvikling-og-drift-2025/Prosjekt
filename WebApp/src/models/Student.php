<?php

namespace models;
use DateMalformedStringException;
use PDO;
use PDOStatement;

/**
 * Represents a student user in the system.
 *
 * This class extends the base `User` class and introduces additional attributes
 * specific to students, such as the study program and enrollment year.
 */
class Student extends User
{
    /** @var string $studyProgram The academic program the student is enrolled in. */
    public string $studyProgram {
        get {
            return $this->studyProgram;
        }
        set {
            $this->studyProgram = $value;
        }
    }

    /** @var int|null $enrollmentYear The year the student enrolled in the program. */
    public ?int $enrollmentYear {
        get {
            return $this->enrollmentYear;
        }
        set {
            $this->enrollmentYear = $value;
        }
    }

    /**
     * Constructs a new Student instance.
     *
     * This constructor initializes a student object with general user attributes
     * from the `User` class and adds student-specific attributes like the study program
     * and enrollment year.
     *
     * @param array $userData Associative array containing student data.<br>
     *        - `id` (int|null) User ID (if null, assigned by the database).<br>
     *        - `firstName` (string) Student's first name.<br>
     *        - `lastName` (string) Student's last name.<br>
     *        - `email` (string) Student's email address.<br>
     *        - `password` (string) Student's password (hashed or plaintext).<br>
     *        - `role` (UserRole) The student's role in the system.<br>
     *        - `resetToken` (string|null) Optional reset token for password recovery.<br>
     *        - `resetTokenCreatedAt` (string|null) Timestamp of password reset request.<br>
     *        - `createdAt` (string|null) Timestamp when the student account was created.<br>
     *        - `updatedAt` (string|null) Timestamp of last student account update.<br>
     *        - `studyProgram` (string) The study program the student is enrolled in.<br>
     *        - `enrollmentYear` (int|null) The year the student enrolled (defaults to current year).<br>
     *
     * @throws DateMalformedStringException
     */
    public function __construct(array $userData)
    {
        parent::__construct($userData);
        $this->studyProgram = $userData['studyProgram'];
        $this->enrollmentYear = $userData['enrollmentYear'] ?? (int)date("Y");
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
        $stmt->bindValue(':id', $this->id ?? null, $this->id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':first_name', $this->firstName, PDO::PARAM_STR);
        $stmt->bindValue(':last_name', $this->lastName, PDO::PARAM_STR);
        $stmt->bindValue(':full_name', $this->fullName, PDO::PARAM_STR);
        $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
        $stmt->bindValue(':password', $this->password, PDO::PARAM_STR);
        $stmt->bindValue(':role', $this->role->value, PDO::PARAM_STR);
        $stmt->bindValue(':studyProgram', $this->studyProgram, PDO::PARAM_STR);
        $stmt->bindValue(':studyYear', $this->enrollmentYear, PDO::PARAM_INT);
    }

}


    // Send a message to a course
    public function sendMessage($studentId, $courseId, $anonymousId, $content)
    {
        // Validate inputs
        if (!InputValidator::isNotEmpty($content)) {
            Logger::error("Message content is empty for student ID: $studentId");
            return false;
        }

        $sql = "INSERT INTO messages (student_id, course_id, anonymous_id, content, created_at)
                VALUES (:studentId, :courseId, :anonymousId, :content, NOW())";
        $stmt = $this->pdo->prepare($sql);

        try {
            return $stmt->execute([
                ':studentId' => $studentId,
                ':courseId' => $courseId,
                ':anonymousId' => $anonymousId,
                ':content' => InputValidator::sanitizeString($content),
            ]);
        } catch (PDOException $e) {
            Logger::error("Failed to send message: " . $e->getMessage());
            return false;
        }
    }

    // View all messages the student has sent
    public function getMessagesByStudent($studentId)
    {
        $sql = "SELECT m.id AS message_id, m.content, m.reply, m.created_at, 
                       c.code AS course_code, c.name AS course_name
                FROM messages m
                JOIN courses c ON m.course_id = c.id
                WHERE m.student_id = :studentId
                ORDER BY m.created_at DESC"; // Added ORDER BY for consistent ordering
        $stmt = $this->pdo->prepare($sql);

        try {
            $stmt->execute([':studentId' => $studentId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Logger::error("Failed to fetch messages for student ID $studentId: " . $e->getMessage());
            return [];
        }
    }

    // View a specific message and its reply
    public function getMessageWithReply($messageId, $studentId)
    {
        $sql = "SELECT m.id AS message_id, m.content, m.reply, m.created_at, 
                       c.code AS course_code, c.name AS course_name
                FROM messages m
                JOIN courses c ON m.course_id = c.id
                WHERE m.id = :messageId AND m.student_id = :studentId";
        $stmt = $this->pdo->prepare($sql);

        try {
            $stmt->execute([
                ':messageId' => $messageId,
                ':studentId' => $studentId,
            ]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Logger::error("Failed to fetch message ID $messageId for student ID $studentId: " . $e->getMessage());
            return null;
        }
    }

    // Get a list of all courses available to the student
    public function getAvailableCourses()
    {
        $sql = "SELECT id, code, name FROM courses"; // Removed unnecessary pin_code
        try {
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Logger::error("Failed to fetch available courses: " . $e->getMessage());
            return [];
        }
    }
}
