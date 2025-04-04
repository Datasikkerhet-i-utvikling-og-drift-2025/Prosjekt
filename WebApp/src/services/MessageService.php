<?php

namespace services;

use DateMalformedStringException;
use Exception;
use finfo;
use helpers\AuthHelper;
use helpers\InputValidator;
use helpers\Logger;
use helpers\ApiResponse;
use managers\JWTManager;
use managers\SessionManager;
use repositories\UserRepository;
use RuntimeException;
use Random\RandomException;
use repositories\MessageRepository;
use repositories\CommentRepository;
use repositories\ReportRepository;
use repositories\CourseRepository;
use repositories\AnonymousIdRepository;
use repositories\LecturerRepository;

/** Class AuthService   
 * Handles authentication and message-related logic.
 * Compatible with both web and mobile clients.
 * Uses session manager for secure session lifecycle handling.
 */

class MessageService
{
    private MessageRepository $messageRepository;
    private CommentRepository $commentRepository;
    private ReportRepository $reportRepository;
    private CourseRepository $courseRepository;
    private AnonymousIdRepository $anonymousIdRepository;
    private LecturerRepository $lecturerRepository;

    /**
     * MessageService constructor.
     *
     * @param MessageRepository $messageRepository
     * @param CommentRepository $commentRepository
     * @param ReportRepository $reportRepository
     * @param CourseRepository $courseRepository
     * @param AnonymousIdRepository $anonymousIdRepository
     * @param LecturerRepository $lecturerRepository
     */
    public function __construct(
        MessageRepository $messageRepository,
        CommentRepository $commentRepository,
        ReportRepository $reportRepository,
        CourseRepository $courseRepository,
        AnonymousIdRepository $anonymousIdRepository,
        LecturerRepository $lecturerRepository
    ) {
        $this->messageRepository = $messageRepository;
        $this->commentRepository = $commentRepository;
        $this->reportRepository = $reportRepository;
        $this->lecturerRepository = $lecturerRepository;
        $this->courseRepository = $courseRepository;
        $this->anonymousIdRepository = $anonymousIdRepository;
    }

    /**
     * Handle the creation of a new message to a specific course.
     * Accepts POST requests with course ID and message content.
     * Responds with success status and message ID.
     */
    public function createMessage(): 
    {
        //Sanitize all input data
        $_POST = array_map([InputValidator::class, 'sanitizeString'], $_POST);

        // Validate the input data
       $validatedData = InputValidator::validateMessageCreation($_POST);
        if ($validatedData['success']) {
            $message = new Message();
            $message->courseId = $validatedData['data']['courseId'];
            $message->studentId = $validatedData['data']['studentId'];
            $message->anonymousId = $validatedData['data']['anonymousId'];
            $message->content = $validatedData['data']['content'];

            $result = $this->messageRepository->createMessage($message);
            if ($result) {
                ApiHelper::sendApiResponse(201, ['success' => true, 'messageId' => $result]);
            } else {
                ApiHelper::sendApiResponse(500, ['success' => false, 'error' => 'Failed to create message.']);
            }
        } else {
            ApiHelper::sendApiResponse(400, ['success' => false, 'error' => 'Validation failed.', 'details' => $validatedData['errors']]);
        }
    }

    /**
     * Sends a message to a specific course.
     *
     * @param int $courseId
     * @param string $content
     * @param int|null $studentId
     * @param int|null $anonymousId
     * @return ApiResponse
     * @throws Exception
     */
    public function sendMessage(int $courseId, string $content, ?int $studentId = null, ?int $anonymousId = null): ApiResponse
    {
        // Sanitize and validate input
        $content = InputValidator::sanitizeString($content);
        if (!InputValidator::isNotEmpty($content)) {
            return new ApiResponse(false, 'Message content cannot be empty.', null, ['courseId' => $courseId]);
        }

        if (!InputValidator::isValidInteger($courseId)) {
            return new ApiResponse(false, 'Invalid course ID.', null, ['courseId' => $courseId]);
        }

        // Check if the course exists
        $course = $this->courseRepository->getCourseById($courseId);
        if (!$course) {
            return new ApiResponse(false, 'Course not found.', null, ['courseId' => $courseId]);
        }

        // Create the message
        $message = new Message();
        $message->courseId = $courseId;
        $message->content = $content;
        $message->studentId = $studentId;
        $message->anonymousId = $anonymousId;

        $result = $this->messageRepository->createMessage($message);
        if ($result) {
            return new ApiResponse(true, 'Message sent successfully.', ['messageId' => $result]);
        } else {
            return new ApiResponse(false, 'Failed to send message.', null, ['courseId' => $courseId]);
        }
    }
        
    
    /**
    * Retrieves messages from a specificmCourse.
    * Accepts POST requests withmCourse ID as a parameter.
    * Responds with success status and message data.
    *
    * @param int $courseId
    * @return ApiResponse
    * @throws RandomException|DateMalformedStringException
    * @throws Exception
    */
    public function getMessagesFromCourse(int $courseId): ApiResponse
    {
        // Validate courseID
        if (!InputValidator::isValidCourseId($courseId)) {
            return new ApiResponse(false, 'Invalid course ID.', null, ['courseId' => $courseId]);
        }

        $messages = $this->messageRepository->getMessagesByCourse($courseId);
        if ($messages === false) {
            return new ApiResponse(false, 'Failed to retrieve messages.', null, ['courseId' => $courseId]);
        }

        return new ApiResponse(true, 'Messages retrieved successfully.', $messages);
    }

    /**
     * Reports a message.
     * Accepts POST requests with message ID and report reason.
     * Responds with success status and report ID.
     *
     * @param int $messageId
     * @param string $reason
     * @return ApiResponse
     * @throws RandomException|DateMalformedStringException
     * @throws Exception
     */
    public function reportMessage(int $messageId, string $reason): ApiResponse
    {
        // Sanitize and validate input
        $reason = InputValidator::sanitizeString($reason);
        if (!InputValidator::isNotEmpty($reason)) {
            return new ApiResponse(false, 'Report reason cannot be empty.', null, ['messageId' => $messageId]);
        }

        if (!InputValidator::isValidInteger($messageId)) {
            return new ApiResponse(false, 'Invalid message ID.', null, ['messageId' => $messageId]);
        }

        // Check if the message exists
        $message = $this->messageRepository->getMessageById($messageId);
        if (!$message) {
            return new ApiResponse(false, 'Message not found.', null, ['messageId' => $messageId]);
        }

        // Create the report
        $result = $this->reportRepository->reportMessage($messageId, $reason);
        if ($result) {
            return new ApiResponse(true, 'Message reported successfully.', ['messageId' => $messageId]);
        } else {
            return new ApiResponse(false, 'Failed to report message.', null, ['messageId' => $messageId]);
        }
    }
    /**
     * Sends a comment to a specific message.
     * Accepts POST requests with message ID and comment content.
     * Responds with success status and comment ID.
     *
     * @param int $messageId
     * @param string $content
     * @return ApiResponse
     * @throws RandomException|DateMalformedStringException
     * @throws Exception
     */
    public function sendComment(int $messageId, string $content): ApiResponse
    {
        // Sanitize and validate input
        $content = InputValidator::sanitizeString($content);
        if (!InputValidator::isNotEmpty($content)) {
            return new ApiResponse(false, 'Comment content cannot be empty.', null, ['messageId' => $messageId]);
        }

        if (!InputValidator::isValidInteger($messageId)) {
            return new ApiResponse(false, 'Invalid message ID.', null, ['messageId' => $messageId]);
        }

        // Check if the message exists
        $message = $this->messageRepository->getMessageById($messageId);
        if (!$message) {
            return new ApiResponse(false, 'Message not found.', null, ['messageId' => $messageId]);
        }

        // Create the comment
        $comment = new Comment();
        $comment->messageId = $messageId;
        $comment->content = $content;

        $result = $this->commentRepository->createComment($comment);
        if ($result) {
            return new ApiResponse(true, 'Comment sent successfully.', ['commentId' => $result]);
        } else {
            return new ApiResponse(false, 'Failed to send comment.', null, ['messageId' => $messageId]);
        }
    }


    /**
     * Retrieves comments for a specific message.
     * Accepts GET requests with message ID as a parameter.
     * Responds with success status and comment data.
     *
     * @param int $messageId
     * @return ApiResponse
     * @throws RandomException|DateMalformedStringException
     * @throws Exception
     */
    public function getCommentsForMessage(int $messageId): ApiResponse
    {
        // Validate courseID
        if (!InputValidator::isValidMessageId($messageId)) {
            return new ApiResponse(false, 'Invalid message ID.', null, ['messageId' => $messageId]);
        }

    $comments = $this->commentRepository->getCommentsByMessageId($messageId);
    if ($comments === false) {
        return new ApiResponse(false, 'Failed to retrieve comments.', null, ['messageId' => $messageId]);
    }

    return new ApiResponse(true, 'Comments retrieved successfully.', $comments);
    }

    /// Retrieves all messages for a specific course.
    /// Accepts GET requests with course ID as a parameter.
    /// Responds with success status and message data.
    ///
    /// @param int $courseId
    /// @return ApiResponse
    /// @throws RandomException|DateMalformedStringException
    /// @throws Exception
    public function getMessagesForCourse(int $courseId): ApiResponse
    {
        // Validate courseID
        if (!InputValidator::isValidCourseId($courseId)) {
            return new ApiResponse(false, 'Invalid course ID.', null, ['courseId' => $courseId]);
        }

        $messages = $this->messageRepository->getMessagesByCourseId($courseId);
        if ($messages === false) {
            return new ApiResponse(false, 'Failed to retrieve messages.', null, ['courseId' => $courseId]);
        }

        return new ApiResponse(true, 'Messages retrieved successfully.', $messages);
    }
}
   