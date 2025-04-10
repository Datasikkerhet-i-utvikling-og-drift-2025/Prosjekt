<?php

namespace repositories;

use helpers\InputValidator;
use helpers\Logger;
use managers\DatabaseManager;
use models\Course;

use PDO;

/**
 * Repository class for handling course-related database operations.
 */
class CourseRepository
{
    private DatabaseManager $db;

    /**
     * Constructs the CourseRepository.
     *
     * @param DatabaseManager $db The database service instance.
     */
    public function __construct(DatabaseManager $db)
    {
        $this->db = $db;
    }


    /**
     * Creates a new course in the database.
     *
     * @param string $code Unique course code.
     * @param string $name Name of the course.
     * @param int $lecturerId ID of the lecturer responsible for the course.
     * @param string $pinCode 4-digit access code for the course.
     *
     * @return bool Returns true if the course was created successfully, false otherwise.
     */
    public function createCourse(Course $course): bool
    {
        $sql = "INSERT INTO courses (code, name, lecturer_id, pin_code, created_at)
                VALUES (:code, :name, :lecturerId, :pinCode, NOW())";

        $stmt = $this->db->prepareStmt(
            $sql,
            fn($stmt) => $course->bindCourseDataForDbStmt($stmt)
        );

        return $this->db->executeTransaction("Saving user data in database");
    }


    /**
     * Retrieves a course by its ID.
     *
     * @param int $courseId The ID of the course.
     *
     * @return Course|null Returns a Course object if found, otherwise null.
     */
    public function getCourseById(int $courseId): ?Course
    {
        if (!InputValidator::isValidInteger($courseId)) {
            Logger::error("Invalid course ID: $courseId");
            return null;
        }

        $sql = "SELECT * FROM courses WHERE id = :id";
        $stmt = $this->db->prepareStmt($sql);
        //$this->db->bindSingleValueToSqlStmt($stmt, ":id", $courseId);

        $logger = "Fetching course by ID: " . $courseId;
        $data = $this->db->fetchSingle($stmt, $logger);

        return $data ? new Course($data) : null;
    }


    /**
     * Retrieves all courses from the database.
     *
     * @return Course[] Returns an array of Course objects.
     */
    public function getAllCourses(): array
    {
        $sql = "SELECT * FROM courses";
        $stmt = $this->db->prepareStmt($sql);

        $logger = "Fetching all courses from the database";
        $coursesData = $this->db->fetchAll($stmt, $logger);

        $courses = [];
        foreach ($coursesData as $data) {
            $courses[] = new Course($data);
        }

        return $courses;
    }


    /**
     * Updates an existing course in the database.
     *
     * @param int $id Course ID.
     * @param string $code Unique course code.
     * @param string $name Name of the course.
     * @param int $lecturerId ID of the lecturer responsible for the course.
     * @param string $pinCode 4-digit access code for the course.
     *
     * @return bool Returns true if the course was updated successfully, false otherwise.
     */
    public function updateCourse(int $id, string $code, string $name, int $lecturerId, string $pinCode): bool
    {
        if (!InputValidator::isValidInteger($id) || !InputValidator::isValidInteger($lecturerId)) {
            Logger::error("Invalid course or lecturer ID: $id, $lecturerId");
            return false;
        }

        if (!InputValidator::isNotEmpty($code) || !InputValidator::isNotEmpty($name) || !InputValidator::isNotEmpty($pinCode)) {
            Logger::error("Course update failed: Missing required fields");
            return false;
        }

        $sql = "UPDATE courses 
                SET code = :code, 
                    name = :name, 
                    lecturer_id = :lecturerId, 
                    pin_code = :pinCode, 
                    created_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepareStmt($sql);
        //$this->db->bindArrayToSqlStmt($stmt, [':id', ':code', ':name', ':lecturerId', ':pinCode'],
        //    [$id, $code, $name, $lecturerId, $pinCode]);
        $logger = "Updating course ID: " . $id;

        return $this->db->executeTransaction($stmt, $logger);
    }


    /**
     * Deletes a course from the database.
     *
     * @param int $courseId The ID of the course.
     *
     * @return bool Returns true if the course was deleted successfully, false otherwise.
     */
    public function deleteCourse(int $courseId): bool
    {
        if (!InputValidator::isValidInteger($courseId)) {
            Logger::error("Invalid course ID: $courseId");
            return false;
        }

        $sql = "DELETE FROM courses WHERE id = :id";
        $stmt = $this->db->prepareStmt($sql);
        //$this->db->bindSingleValueToSqlStmt($stmt, ":id", $courseId);

        $logger = "Deleting course ID: " . $courseId;
        return $this->db->executeTransaction($stmt, $logger);
    }
    public function findCourseByPin(string $pin): ?array
    {
        // Validate the pin
        if (!InputValidator::isNotEmpty($pin)) {
            Logger::error("Invalid pin: Pin cannot be empty.");
            return null;
        }

        $sql = "SELECT id, code, name, pin_code, lecturer_id FROM courses WHERE pin_code = :pin_code";

        // Prepare the statement
        $stmtPrepared = $this->db->prepareStmt($sql, function ($stmt) use ($pin) {
            $stmt->bindValue(':pin_code', $pin, PDO::PARAM_STR);
        });

        if (!$stmtPrepared) {
            Logger::error("Failed to prepare statement for finding course by pin.");
            return null;
        }

        // Fetch the course
        $loggerMessage = "Fetching course by pin: $pin";
        $course = $this->db->fetchSingle($loggerMessage);

        return $course ?: null;
    }
}
