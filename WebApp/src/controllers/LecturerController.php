<?php

namespace controllers;

require_once __DIR__ . '/../helpers/ApiHelper.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';
require_once __DIR__ . '/../models/Course.php';
require_once __DIR__ . '/../models/Message.php';
require_once __DIR__ . '/../helpers/Logger.php';

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
        try {
            $courses = $this->courseModel->getCoursesByLecturer($lecturerId);
            Logger::info("Courses retrieved successfully for lecturer ID: $lecturerId");
            ApiHelper::sendResponse(200, $courses, 'Courses retrieved successfully.');
        } catch (Exception $e) {
            Logger::error("Failed to retrieve courses for lecturer ID: $lecturerId. Error: " . $e->getMessage());
            ApiHelper::sendError(500, 'Failed to retrieve courses.');
        }
    }

    // Get messages for a specific course
    public function getMessagesForCourse()
    {
        AuthHelper::requireRole('lecturer');

        $input = ApiHelper::getJsonInput();
        ApiHelper::validateRequest(['course_id'], $input);

        $lecturerId = AuthHelper::getUserId();
        try {
            $course = $this->courseModel->getCourseById($input['course_id']);

            // Ensure the course belongs to the logged-in lecturer
            if (!$course || $course->getLecturerId() !== $lecturerId) {
                Logger::error("Unauthorized access to course ID: {$input['course_id']} by lecturer ID: $lecturerId");
                ApiHelper::sendError(403, 'Unauthorized to view messages for this course.');
            }

            $messages = $this->messageModel->getMessagesByCourse($input['course_id']);
            Logger::info("Messages retrieved successfully for course ID: {$input['course_id']} by lecturer ID: $lecturerId");
            ApiHelper::sendResponse(200, $messages, 'Messages retrieved successfully.');
        } catch (Exception $e) {
            Logger::error("Failed to retrieve messages for course ID: {$input['course_id']}. Error: " . $e->getMessage());
            ApiHelper::sendError(500, 'Failed to retrieve messages.');
        }
    }

    // Reply to a student's message
    public function replyToMessage()
    {
        AuthHelper::requireRole('lecturer');

        $input = ApiHelper::getJsonInput();
        ApiHelper::validateRequest(['message_id', 'reply'], $input);

        try {
            $message = $this->messageModel->getMessageById($input['message_id']);

            // Ensure the message belongs to one of the lecturer's courses
            $lecturerId = AuthHelper::getUserId();
            $course = $this->courseModel->getCourseById($message['course_id']);

            if (!$course || $course->getLecturerId() !== $lecturerId) {
                Logger::error("Unauthorized reply attempt for message ID: {$input['message_id']} by lecturer ID: $lecturerId");
                ApiHelper::sendError(403, 'Unauthorized to reply to this message.');
            }

            $result = $this->messageModel->updateMessageReply($input['message_id'], $input['reply']);
            if ($result) {
                Logger::info("Reply sent successfully for message ID: {$input['message_id']} by lecturer ID: $lecturerId");
                ApiHelper::sendResponse(200, [], 'Reply sent successfully.');
            } else {
                throw new Exception("Database operation failed");
            }
        } catch (Exception $e) {
            Logger::error("Failed to send reply for message ID: {$input['message_id']}. Error: " . $e->getMessage());
            ApiHelper::sendError(500, 'Failed to send reply.');
        }
    }

    // Mark a message as resolved (optional feature)
    public function markMessageAsResolved()
    {
        AuthHelper::requireRole('lecturer');

        $input = ApiHelper::getJsonInput();
        ApiHelper::validateRequest(['message_id'], $input);

        try {
            $message = $this->messageModel->getMessageById($input['message_id']);

            // Ensure the message belongs to one of the lecturer's courses
            $lecturerId = AuthHelper::getUserId();
            $course = $this->courseModel->getCourseById($message['course_id']);

            if (!$course || $course->getLecturerId() !== $lecturerId) {
                Logger::error("Unauthorized attempt to mark message ID: {$input['message_id']} as resolved by lecturer ID: $lecturerId");
                ApiHelper::sendError(403, 'Unauthorized to mark this message as resolved.');
            }

            $result = $this->messageModel->markAsResolved($input['message_id']);
            if ($result) {
                Logger::info("Message ID: {$input['message_id']} marked as resolved by lecturer ID: $lecturerId");
                ApiHelper::sendResponse(200, [], 'Message marked as resolved.');
            } else {
                throw new Exception("Database operation failed");
            }
        } catch (Exception $e) {
            Logger::error("Failed to mark message ID: {$input['message_id']} as resolved. Error: " . $e->getMessage());
            ApiHelper::sendError(500, 'Failed to mark message as resolved.');
        }
    }
}
