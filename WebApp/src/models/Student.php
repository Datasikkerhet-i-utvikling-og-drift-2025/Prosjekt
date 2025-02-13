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
        $this->studyProgram = $userData['study_program'];
        $this->enrollmentYear = $userData['cohort_year'] ?? (int)date("Y");
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

        $stmt->bindValue(':studyProgram', $this->studyProgram, PDO::PARAM_STR);
        $stmt->bindValue(':studyYear', $this->enrollmentYear, PDO::PARAM_INT);

        $stmt->bindValue(':imagePath', $this->imagePath ?? null, isset($this->imagePath) ? PDO::PARAM_STR : PDO::PARAM_NULL);
    }

}

