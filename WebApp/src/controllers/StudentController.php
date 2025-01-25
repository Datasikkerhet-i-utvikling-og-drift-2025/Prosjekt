<?php

require_once '../src/helpers/ApiHelper.php';
require_once '../src/helpers/AuthHelper.php';
require_once '../src/helpers/InputValidator.php';
require_once '../src/models/Course.php';
require_once '../src/models/Message.php';

class StudentController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // View available courses
    public function getCourses()
    {
        AuthHelper::requireRole('student');

        $stmt = $this->pdo->prepare("SELECT * FROM courses");
        $stmt->execute();
        $coursesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Map database rows to Course objects
        $courses = array_map(function ($courseData) {
            return Course::fromArray($courseData);
        }, $coursesData);

        ApiHelper::sendResponse(200, $courses, 'Courses retrieved successfully.');
    }

    // View messages sent by the student
    public function getMyMessages()
    {
        AuthHelper::requireRole('student');

        $studentId = AuthHelper::getUserId();
        $stmt = $this->pdo->prepare("SELECT * FROM messages WHERE student_id = :student_id");
        $stmt->execute(['student_id' => $studentId]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        ApiHelper::sendResponse(200, $messages, 'Messages retrieved successfully.');
    }

    // Send a new message to a course
    public function sendMessage()
    {
        AuthHelper::requireRole('student');

        $input = ApiHelper::getJsonInput();

        // Validate input
        $validationRules = [
            'course_id' => ['required' => true],
            'content' => ['required' => true, 'min' => 1]
        ];
        $validation = InputValidator::validateInputs($input, $validationRules);

        if (!empty($validation['errors'])) {
            ApiHelper::sendError(400, 'Validation failed.', $validation['errors']);
        }

        $sanitized = $validation['sanitized'];

        // Generate an anonymous ID for the student
        $anonymousId = ApiHelper::generateUuid();
        $studentId = AuthHelper::getUserId();

        // Save the message
        $stmt = $this->pdo->prepare("
            INSERT INTO messages (student_id, course_id, anonymous_id, content, created_at)
            VALUES (:student_id, :course_id, :anonymous_id, :content, NOW())
        ");
        $result = $stmt->execute([
            'student_id' => $studentId,
            'course_id' => $sanitized['course_id'],
            'anonymous_id' => $anonymousId,
            'content' => $sanitized['content']
        ]);

        if ($result) {
            ApiHelper::sendResponse(201, [], 'Message sent successfully.');
        } else {
            ApiHelper::sendError(500, 'Failed to send the message.');
        }
    }
}
