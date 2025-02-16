<?php

namespace controllers;

class StudentController
{
    private $courseModel;
    private $messageModel;

    public function __construct($pdo)
    {
        $this->courseModel = new Course($pdo);
        $this->messageModel = new Message($pdo);
    }

    // View available courses
    public function getCourses()
    {
        AuthHelper::requireRole('student');

        try {
            $courses = $this->courseModel->getAllCourses();
            Logger::info("Courses retrieved successfully for student ID: " . AuthHelper::getUserId());
            ApiHelper::sendResponse(200, $courses, 'Courses retrieved successfully.');
        } catch (Exception $e) {
            Logger::error("Failed to retrieve courses. Error: " . $e->getMessage());
            ApiHelper::sendError(500, 'Failed to retrieve courses.');
        }
    }

    // View messages sent by the student
    public function getMyMessages()
    {
        AuthHelper::requireRole('student');

        $studentId = AuthHelper::getUserId();

        try {
            $messages = $this->messageModel->getMessagesByStudent($studentId);
            Logger::info("Messages retrieved successfully for student ID: $studentId");
            ApiHelper::sendResponse(200, $messages, 'Messages retrieved successfully.');
        } catch (Exception $e) {
            Logger::error("Failed to retrieve messages for student ID $studentId. Error: " . $e->getMessage());
            ApiHelper::sendError(500, 'Failed to retrieve messages.');
        }
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
            Logger::error("Validation failed for message submission by student ID: " . AuthHelper::getUserId());
            ApiHelper::sendError(400, 'Validation failed.', $validation['errors']);
        }

        $sanitized = $validation['sanitized'];

        try {
            // Generate an anonymous ID for the student
            $anonymousId = ApiHelper::generateUuid();
            $studentId = AuthHelper::getUserId();

            // Save the message
            $result = $this->messageModel->createMessage(
                $studentId,
                $sanitized['course_id'],
                $anonymousId,
                $sanitized['content']
            );

            if ($result) {
                Logger::info("Message sent successfully by student ID: $studentId");
                ApiHelper::sendResponse(201, [], 'Message sent successfully.');
            } else {
                throw new Exception("DatabaseManager operation failed");
            }
        } catch (Exception $e) {
            Logger::error("Failed to send message for student ID: " . AuthHelper::getUserId() . ". Error: " . $e->getMessage());
            ApiHelper::sendError(500, 'Failed to send the message.');
        }
    }
}
