<?php

require_once __DIR__ . '/../helpers/Logger.php';
require_once __DIR__ . '/../helpers/InputValidator.php';

class Course
{
    private $pdo; // PDO instance

    // Properties
    private $id;
    private $code;
    private $name;
    private $lecturerId;
    private $pinCode;

    // Constructor
    public function __construct($pdo, $id = null, $code = null, $name = null, $lecturerId = null, $pinCode = null)
    {
        $this->pdo = $pdo;
        $this->id = $id;
        $this->code = $code;
        $this->name = $name;
        $this->lecturerId = $lecturerId;
        $this->pinCode = $pinCode;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getCode() { return $this->code; }
    public function getName() { return $this->name; }
    public function getLecturerId() { return $this->lecturerId; }
    public function getPinCode() { return $this->pinCode; }

    // Create a new course
    public function createCourse($code, $name, $lecturerId, $pinCode)
    {
        if (!InputValidator::isNotEmpty($code) || !InputValidator::isNotEmpty($name) || !InputValidator::isNotEmpty($pinCode)) {
            Logger::error("Course creation failed: Missing required fields");
            return false;
        }

        if (!InputValidator::isValidInteger($lecturerId)) {
            Logger::error("Course creation failed: Invalid lecturer ID");
            return false;
        }

        $sql = "INSERT INTO courses (code, name, lecturer_id, pin_code, created_at)
                VALUES (:code, :name, :lecturerId, :pinCode, NOW())";
        $stmt = $this->pdo->prepare($sql);

        try {
            return $stmt->execute([
                ':code' => InputValidator::sanitizeString($code),
                ':name' => InputValidator::sanitizeString($name),
                ':lecturerId' => (int)$lecturerId,
                ':pinCode' => InputValidator::sanitizeString($pinCode),
            ]);
        } catch (PDOException $e) {
            Logger::error("Failed to create course: " . $e->getMessage());
            return false;
        }
    }

    // Retrieve a course by ID
    public function getCourseById($courseId)
    {
        if (!InputValidator::isValidInteger($courseId)) {
            Logger::error("Invalid course ID: $courseId");
            return null;
        }

        $sql = "SELECT * FROM courses WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        try {
            $stmt->execute([':id' => (int)$courseId]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            return $data ? self::fromArray($data) : null;
        } catch (PDOException $e) {
            Logger::error("Failed to fetch course ID $courseId: " . $e->getMessage());
            return null;
        }
    }

    // Retrieve all courses
    public function getAllCourses()
    {
        $sql = "SELECT * FROM courses";
        $stmt = $this->pdo->query($sql);

        try {
            $courses = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $courses[] = self::fromArray($data);
            }
            return $courses;
        } catch (PDOException $e) {
            Logger::error("Failed to fetch all courses: " . $e->getMessage());
            return [];
        }
    }

    // Update a course
    public function updateCourse($id, $code, $name, $lecturerId, $pinCode)
    {
        if (!InputValidator::isValidInteger($id)) {
            Logger::error("Invalid course ID: $id");
            return false;
        }

        if (!InputValidator::isValidInteger($lecturerId)) {
            Logger::error("Invalid lecturer ID: $lecturerId");
            return false;
        }

        if (!InputValidator::isNotEmpty($code) || !InputValidator::isNotEmpty($name) || !InputValidator::isNotEmpty($pinCode)) {
            Logger::error("Course update failed: Missing required fields");
            return false;
        }

        $sql = "UPDATE courses SET code = :code, name = :name, lecturer_id = :lecturerId, pin_code = :pinCode, updated_at = NOW()
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        try {
            return $stmt->execute([
                ':id' => (int)$id,
                ':code' => InputValidator::sanitizeString($code),
                ':name' => InputValidator::sanitizeString($name),
                ':lecturerId' => (int)$lecturerId,
                ':pinCode' => InputValidator::sanitizeString($pinCode),
            ]);
        } catch (PDOException $e) {
            Logger::error("Failed to update course ID $id: " . $e->getMessage());
            return false;
        }
    }

    // Delete a course
    public function deleteCourse($courseId)
    {
        if (!InputValidator::isValidInteger($courseId)) {
            Logger::error("Invalid course ID: $courseId");
            return false;
        }

        $sql = "DELETE FROM courses WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        try {
            return $stmt->execute([':id' => (int)$courseId]);
        } catch (PDOException $e) {
            Logger::error("Failed to delete course ID $courseId: " . $e->getMessage());
            return false;
        }
    }

    // Static method to create a Course instance from a database row
    public static function fromArray(array $data)
    {
        return new self(
            null,
            $data['id'] ?? null,
            $data['code'] ?? null,
            $data['name'] ?? null,
            $data['lecturer_id'] ?? null,
            $data['pin_code'] ?? null
        );
    }
}
