<?php

namespace controllers;

use Exception;

require_once __DIR__ . '/../helpers/ApiHelper.php';
require_once __DIR__ . '/../helpers/InputValidator.php';
require_once __DIR__ . '/../models/Message.php';
require_once __DIR__ . '/../models/Course.php';
require_once __DIR__ . '/../models/Comment.php';
require_once __DIR__ . '/../helpers/Logger.php';

class GuestController
{
    private $messageModel;
    private $courseModel;
    private $commentModel;

    public function __construct($pdo)
    {
        $this->messageModel = new Message($pdo);
        $this->courseModel = new Course($pdo);
        $this->commentModel = new Comment($pdo);
    }

    public function getMessages()
    {
        try {
            // Fetch public messages
            $messages = $this->messageModel->getPublicMessages();

            foreach ($messages as &$message) {
                $message['comments'] = $this->commentModel->getCommentsByMessageId($message['message_id']);
            }

            Logger::info("Public messages retrieved successfully.");
            ApiHelper::sendResponse(200, $messages, 'Public messages retrieved successfully.');
        } catch (Exception $e) {
            Logger::error("Error retrieving public messages: " . $e->getMessage());
            ApiHelper::sendError(500, 'Failed to retrieve public messages.');
        }
    }


    // View messages for a course (requires PIN code)
    public function viewMessages()
    {
        $input = ApiHelper::getJsonInput();
        ApiHelper::validateRequest(['course_id', 'pin_code'], $input);

        // Fetch course and validate PIN code
        $course = $this->courseModel->getCourseById($input['course_id']);
        if (!$course || $course->getPinCode() !== $input['pin_code']) {
            Logger::error("Unauthorized access attempt for course ID: {$input['course_id']}");
            ApiHelper::sendError(403, 'Invalid course ID or PIN code.');
        }

        // Fetch messages and comments
        $messages = $this->messageModel->getMessagesByCourse($input['course_id']);
        foreach ($messages as &$message) {
            $message['comments'] = $this->commentModel->getCommentsByMessageId($message['message_id']);
        }

        Logger::info("Messages retrieved successfully for course ID: {$input['course_id']}");
        ApiHelper::sendResponse(200, $messages, 'Messages retrieved successfully.');
    }

    // Report a message
    public function reportMessage()
    {
        $input = ApiHelper::getJsonInput();
        ApiHelper::validateRequest(['message_id', 'report_reason'], $input);

        if (!InputValidator::isNotEmpty($input['report_reason'])) {
            Logger::error("Report reason is empty for message ID: {$input['message_id']}");
            ApiHelper::sendError(400, 'Report reason cannot be empty.');
        }

        $result = $this->messageModel->reportMessage($input['message_id'], InputValidator::sanitizeString($input['report_reason']));

        if ($result) {
            Logger::info("Message ID: {$input['message_id']} reported successfully.");
            ApiHelper::sendResponse(200, [], 'Message reported successfully.');
        } else {
            Logger::error("Failed to report message ID: {$input['message_id']}");
            ApiHelper::sendError(500, 'Failed to report the message.');
        }
    }

    // Add a comment to a message
    public function addComment()
    {
        $input = ApiHelper::getJsonInput();

        // Validate input
        $validationRules = [
            'message_id' => ['required' => true],
            'guest_name' => ['required' => false, 'max' => 50],
            'comment' => ['required' => true, 'min' => 1]
        ];
        $validation = InputValidator::validateInputs($input, $validationRules);

        if (!empty($validation['errors'])) {
            Logger::error("Failed to add comment: Validation errors.", $validation['errors']);
            ApiHelper::sendError(400, 'Validation failed.', $validation['errors']);
        }

        $sanitized = $validation['sanitized'];

        // Add the comment
        $result = $this->commentModel->addComment(
            $sanitized['message_id'],
            $sanitized['guest_name'],
            $sanitized['comment']
        );

        if ($result) {
            Logger::info("Comment added successfully to message ID: {$sanitized['message_id']}");
            header('Location: /guests/dashboard');
        } else {
            Logger::error("Failed to add comment to message ID: {$sanitized['message_id']}");
            ApiHelper::sendError(500, 'Failed to add comment.');
        }
    }
}
