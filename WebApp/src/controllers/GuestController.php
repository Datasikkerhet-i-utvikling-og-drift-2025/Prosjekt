<?php

require_once '../src/helpers/ApiHelper.php';
require_once '../src/helpers/InputValidator.php';
require_once '../src/models/Message.php';
require_once '../src/models/Course.php';
require_once '../src/models/Comment.php';

class GuestController {
    private $messageModel;
    private $courseModel;
    private $commentModel;

    public function __construct($pdo) {
        $this->messageModel = new Message($pdo);
        $this->courseModel = new Course($pdo);
        $this->commentModel = new Comment($pdo);
    }

    // View messages for a course (requires PIN code)
    public function viewMessages() {
        $input = ApiHelper::getJsonInput();
        ApiHelper::validateRequest(['course_id', 'pin_code'], $input);

        $course = $this->courseModel->getCourseById($input['course_id']);
        if (!$course || $course['pin_code'] !== $input['pin_code']) {
            ApiHelper::sendError(403, 'Invalid course ID or PIN code.');
        }

        $messages = $this->messageModel->getMessagesByCourse($input['course_id']);
        foreach ($messages as &$message) {
            // Fetch comments for each message
            $message['comments'] = $this->commentModel->getCommentsByMessageId($message['id']);
        }

        ApiHelper::sendResponse(200, $messages, 'Messages retrieved successfully.');
    }

    // Report a message
    public function reportMessage() {
        $input = ApiHelper::getJsonInput();
        ApiHelper::validateRequest(['message_id', 'report_reason'], $input);

        $result = $this->messageModel->reportMessage($input['message_id'], $input['report_reason']);

        if ($result) {
            ApiHelper::sendResponse(200, [], 'Message reported successfully.');
        } else {
            ApiHelper::sendError(500, 'Failed to report the message.');
        }
    }

    // Add a comment to a message
    public function addComment() {
        $input = ApiHelper::getJsonInput();

        // Validate input
        $validationRules = [
            'message_id' => ['required' => true],
            'guest_name' => ['required' => true, 'min' => 3, 'max' => 50],
            'content' => ['required' => true, 'min' => 1]
        ];
        $validation = InputValidator::validateInputs($input, $validationRules);

        if (!empty($validation['errors'])) {
            ApiHelper::sendError(400, 'Validation failed.', $validation['errors']);
        }

        $sanitized = $validation['sanitized'];

        // Add the comment
        $result = $this->commentModel->addComment(
            $sanitized['message_id'],
            $sanitized['guest_name'],
            $sanitized['content']
        );

        if ($result) {
            ApiHelper::sendResponse(201, [], 'Comment added successfully.');
        } else {
            ApiHelper::sendError(500, 'Failed to add comment.');
        }
    }
}
