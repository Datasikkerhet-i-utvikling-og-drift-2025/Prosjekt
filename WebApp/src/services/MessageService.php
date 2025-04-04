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
use repositories\SubjectRepository;
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
    private SubjectRepository $subjectRepository;
    private CourseRepository $courseRepository;
    private AnonymousIdRepository $anonymousIdRepository;

    /**
     * MessageService constructor.
     *
     * @param MessageRepository $messageRepository
     * @param CommentRepository $commentRepository
     * @param ReportRepository $reportRepository
     * @param SubjectRepository $subjectRepository
     * @param CourseRepository $courseRepository
     * @param AnonymousIdRepository $anonymousIdRepository
     */
    public function __construct(
        MessageRepository $messageRepository,
        CommentRepository $commentRepository,
        ReportRepository $reportRepository,
        SubjectRepository $subjectRepository,
        CourseRepository $courseRepository,
        AnonymousIdRepository $anonymousIdRepository
    ) {
        $this->messageRepository = $messageRepository;
        $this->commentRepository = $commentRepository;
        $this->reportRepository = $reportRepository;
        $this->subjectRepository = $subjectRepository;
        $this->courseRepository = $courseRepository;
        $this->anonymousIdRepository = $anonymousIdRepository;
    }

    /**
     * Handle the creation of a new message to a specific course.
     * Accepts POST requests with course ID and message content.
     * Responds with success status and message ID.
     */
    public function createMessage(): void
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
    
        /**
        * Retrieves messages from a specific subject.
        * Accepts GET requests with subject ID as a parameter.
        * Responds with success status and message data.
        *
        * @param int $courseId
        * @return ApiResponse
        * @throws RandomException|DateMalformedStringException
        * @throws Exception
        */
        public function getMessagesFromSubject(int $courseId): ApiResponse
        {
            // Validate courseID
            if (!InputValidator::isValidCourseId($courseId)) {
                return new ApiResponse(false, 'Invalid course ID.', null, ['courseId' => $courseId]);
            }

            $messages = $this->messageRepository->getMessagesBySubjectId($courseId);
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
            // Validate and sanitize input
            $reason = InputValidator::sanitizeString($reason)
            $validation = InputValidator::validateReport($messageId, $reason);

            if (!empty($validation['errors'])) {
                return new ApiResponse(false, 'Validation failed.', null, $validation['errors']);
            }

            $report = new Report();
            $report->messageId = $messageId;
            $report->reason = $reason;

            $result = $this->reportRepository->createReport($report);
            if ($result) {
                return new ApiResponse(true, 'Message reported successfully.', ['reportId' => $result]);
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
            // validatew and sanitize input
            $content = InputValidator::sanitizeString($content);
            $validation = InputValidator::validateComment($messageId, $content);
            if (!empty($validation['errors'])) {
                return new ApiResponse(false, 'Validation failed.', null, $validation['errors']);
            }

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
}   