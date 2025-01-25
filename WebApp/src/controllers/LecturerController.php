<?php

require_once '../src/helpers/ApiHelper.php';
require_once '../src/helpers/AuthHelper.php';
require_once '../src/models/Course.php';
require_once '../src/models/Message.php';

class LecturerController
{
    private $courseModel;
    private $messageModel;

    public function __construct($pdo)
    {
        $this->courseModel = new Course($pdo);
        $this->messageModel = new Message($pdo);
    }

    // Get all courses assigned to the lecturer
    public function getCourses()
    {
        AuthHelper::requireRole('lecturer');

        $lecturerId = AuthHelper::getUserId();
        $courses = $this->courseModel->getCoursesByLecturer($lecturerId);

        ApiHelper::sendResponse(200, $courses, 'Courses retrieved successfully.');
    }

    // Get messages for a specific course
    public function getMessagesForCourse()
    {
        AuthHelper::requireRole('lecturer');

        $input = ApiHelper::getJsonInput();
        ApiHelper::validateRequest(['course_id'], $input);

        $lecturerId = AuthHelper::getUserId();
        $course = $this->courseModel->getCourseById($input['course_id']);

        // Ensure the course belongs to the logged-in lecturer
        if (!$course || $course['lecturer_id'] !== $lecturerId) {
            ApiHelper::sendError(403, 'Unauthorized to view messages for this course.');
        }

        $messages = $this->messageModel->getMessagesByCourse($input['course_id']);
        ApiHelper::sendResponse(200, $messages, 'Messages retrieved successfully.');
    }

    // Reply to a student's message
    public function replyToMessage()
    {
        AuthHelper::requireRole('lecturer');

        $input = ApiHelper::getJsonInput();
        ApiHelper::validateRequest(['message_id', 'reply'], $input);

        $message = $this->messageModel->getMessageById($input['message_id']);

        // Ensure the message belongs to one of the lecturer's courses
        $lecturerId = AuthHelper::getUserId();
        $course = $this->courseModel->getCourseById($message['course_id']);

        if (!$course || $course['lecturer_id'] !== $lecturerId) {
            ApiHelper::sendError(403, 'Unauthorized to reply to this message.');
        }

        $result = $this->messageModel->updateMessageReply($input['message_id'], $input['reply']);

        if ($result) {
            ApiHelper::sendResponse(200, [], 'Reply sent successfully.');
        } else {
            ApiHelper::sendError(500, 'Failed to send reply.');
        }
    }

    // Mark a message as resolved (optional feature)
    public function markMessageAsResolved()
    {
        AuthHelper::requireRole('lecturer');

        $input = ApiHelper::getJsonInput();
        ApiHelper::validateRequest(['message_id'], $input);

        $message = $this->messageModel->getMessageById($input['message_id']);

        // Ensure the message belongs to one of the lecturer's courses
        $lecturerId = AuthHelper::getUserId();
        $course = $this->courseModel->getCourseById($message['course_id']);

        if (!$course || $course['lecturer_id'] !== $lecturerId) {
            ApiHelper::sendError(403, 'Unauthorized to mark this message as resolved.');
        }

        $result = $this->messageModel->markAsResolved($input['message_id']);

        if ($result) {
            ApiHelper::sendResponse(200, [], 'Message marked as resolved.');
        } else {
            ApiHelper::sendError(500, 'Failed to mark message as resolved.');
        }
    }
}
