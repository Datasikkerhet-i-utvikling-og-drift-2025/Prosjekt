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
use models\Message;
use repositories\UserRepository;
use RuntimeException;
use Random\RandomException;
use repositories\MessageRepository;
use repositories\LecturerRepository;
use repositories\CommentRepository;
use repositories\ReportRepository;
use repositories\CourseRepository;
use repositories\AnonymousIdRepository;


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
    /**
     * Handle the creation of a new message to a specific course.
     * Accepts POST requests with course ID and message content.
     * Responds with success status and message ID.
     * @param array $userMassage

    public function createMessage(string $userMassage): ApiResponse
    {

        // Validate the input data
       $validatedMessage = InputValidator::sanitizeString($userMessage);
       if (!empty($validatedMessage['errors'])) {
           return new ApiResponse(false, 'Validation failed.', null, $message['errors']);
       }

       $data = $validatedMessage['sanitized'];

        if ($validatedMessage['success']) {
            $message = new Message();
            $message->courseId = $validatedMessage['data']['courseId'];
            $message->studentId = $validatedMessage['data']['studentId'];
            $message->anonymousId = $validatedMessage['data']['anonymousId'];
            $message->content = $validatedMessage['data']['content'];

            $result = $this->messageRepository->createMessage($message);
            if ($result) {
                ApiHelper::sendApiResponse(201, ['success' => true, 'messageId' => $result]);
            } else {
                ApiHelper::sendApiResponse(500, ['success' => false, 'error' => 'Failed to create message.']);
            }
        } else {
            ApiHelper::sendApiResponse(400, ['success' => false, 'error' => 'Validation failed.', 'details' => $validatedData['errors']]);
        }

        return new ApiResponse(true, 'Message successfully created.', $message);
    }
    */

    /**
     * Sends a message to a specific course.
     *
     * @param array $messageData The message data (studentID, courseId, anonymousId, content)
     *
     * @return ApiResponse
     * @throws Exception
     */
    public function sendMessage(array $messageData): ApiResponse
    {
        // Sanitize and validate input
        $validation = InputValidator::validateMessage($messageData);
        if (!empty($validation['errors'])) {
            return new ApiResponse(false, 'Message content cannot be empty.', null, $validation['errors']);
        }

        $data = $validation['sanitized'];

        $message = new Message($data);

        $success = $this->messageRepository->createMessage($message);

        if (!$success){
            return new ApiResponse(false, 'Message content cannot be empty.');
        }

        return new ApiResponse(true, 'Message sent successfully.', $message);

    }
        
    
    /**
    * Retrieves messages from a specificCourse.
    * Accepts POST requests with CourseID as a parameter.
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

        $messages = $this->messageRepository->getMessagesFromCourse($courseId);
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
        if (!InputValidator::isValidInteger($messageId)) {
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
    public function getMessagesbyCourse(int $courseId): ApiResponse
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
     * reply to a student's message
     * Used in LecturerController
     * @param string $messageId
     * @param string $reply
     *
     * @return ApiResponse
     * @throws Exception
     */

    public function replyToMessage(string $messageId, string $reply): ApiResponse
    {
        //validate messageId
        if(!InputValidator::isValidInteger($messageId)) {
            return new ApiResponse(false, 'Invalid message ID.', null, ['messageId' => $messageId]);
        }

        //Sanitize the reply
        $sanitizedReply = InputValidator::sanitizeString($reply);

        //check it the input is empty
        if (!InputValidator::isNotEmpty($sanitizedReply)) {
            return new ApiResponse(false, 'Message content cannot be empty.', ['messageId' => $messageId]);
        }

        $success = $this->lecturerRepository->replyToMessage($messageId, $sanitizedReply);

        if(!$success) {
            return new ApiResponse(false, 'Failed to send reply.', ['messageId' => $messageId]);
        }

        return new ApiResponse(true, 'Reply sent successfully.', ['messageId' => $messageId]);
    }
}
   