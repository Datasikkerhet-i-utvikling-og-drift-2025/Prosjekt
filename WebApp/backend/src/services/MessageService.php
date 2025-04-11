<?php

namespace services;

//use DateMalformedStringException;
use Exception;
use helpers\InputValidator;
use helpers\ApiResponse;
use models\Message;
//use Random\RandomException;
use repositories\MessageRepository;
use repositories\LecturerRepository;
use repositories\CommentRepository;
//use repositories\CourseRepository;
use helpers\GrayLogger;



/** Class AuthService
 * Handles authentication and message-related logic.
 * Compatible with both web and mobile clients.
 * Uses session manager for secure session lifecycle handling.
 */

class MessageService
{
    private MessageRepository $messageRepository;
    private CommentRepository $commentRepository;
    //private CourseRepository $courseRepository;
    private LecturerRepository $lecturerRepository;
    private GrayLogger $logger;

    /**
     * MessageService constructor.
     *
     * @param MessageRepository $messageRepository
     * @param CommentRepository $commentRepository
    // * @param CourseRepository $courseRepository
     * @param LecturerRepository $lecturerRepository
     * @param GrayLogger $logger
     */
    public function __construct(
        MessageRepository $messageRepository,
        CommentRepository $commentRepository,
        //CourseRepository $courseRepository,
        LecturerRepository $lecturerRepository,
        GrayLogger $logger
    ) {
        $this->messageRepository = $messageRepository;
        $this->commentRepository = $commentRepository;
        $this->lecturerRepository = $lecturerRepository;
        //$this->courseRepository = $courseRepository;
        $this->logger = GrayLogger::getInstance();
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
        $this->logger->info('Send Message method has been called', ['payload' => $messageData]);
        // Sanitize and validate input
        $validation = InputValidator::validateMessage($messageData);
        $this->logger->debug('Validation result', $validation);

        if (!empty($validation['errors'])) {
            $this->logger->warning('Validation failed', ['errors' => $validation['errors']]);
            return new ApiResponse(false, 'Message content cannot be empty.', null, $validation['errors']);
        }

        $data = $validation['sanitized'];

        $message = new Message($data);

        $success = $this->messageRepository->createMessage($message);

        if (!$success){
            $this->logger->error('Failed to send message');
            return new ApiResponse(false, 'Message content cannot be empty.');
        }

        $this->logger->info('Message has been sent', ['message' => $message]);
        return new ApiResponse(true, 'Message sent successfully.', $message);

    }

/* Moved to lecturerService
    /**
     * Retrieves messages from a specificCourse.
     * Used in LecturerController.
     * Accepts POST requests with CourseID as a parameter.
     * Responds with success status and message data.
     *
     * @param int $courseId
     * @return ApiResponse
     * @throws Exception
     */
    /*
    public function getMessagesForCourse(int $courseId): ApiResponse
    {
        $this->logger->info('Get Messages by Course ID', ['courseId' => $courseId]);
        // Validate courseID
        if (!InputValidator::isValidInteger($courseId)) {
            $this->logger->warning('');
            return new ApiResponse(false, 'Invalid course ID.', null, ['courseId' => $courseId]);
        }

        $messages = $this->lecturerRepository->getMessagesForCourse($courseId);
        if (!$messages) {
            $this->logger->error('failed to get messages', ['courseId' => $courseId]);
            return new ApiResponse(false, 'Failed to retrieve messages.', null, ['courseId' => $courseId]);
        }

        $this->logger->info('Messages retrieved successfully', ['messages' => $messages]);
        return new ApiResponse(true, 'Messages retrieved successfully.', $messages);
    }
*/
    /**
     * Reports a message.
     * Accepts POST requests with message ID and report reason.
     * Responds with success status and report ID.
     *
     * @param int $messageId
     * @param string $reason
     * @return ApiResponse
     * @throws Exception
     */
    public function reportMessage(int $messageId, string $reason): ApiResponse
    {
        $this->logger->info('Report message method called', ['messageId' => $messageId, 'reason' => $reason]);
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
            $this->logger->error('Failed to retrieve message', ['messageId' => $messageId]);
            return new ApiResponse(false, 'Message not found.', null, ['messageId' => $messageId]);
        }

        // Create the report
        $result = $this->messageRepository->reportMessageById($messageId, $reason);
        if ($result) {
            $this->logger->info('Reported message successfully', ['messageId' => $messageId, 'reason' => $reason]);
            return new ApiResponse(true, 'Message reported successfully.', ['messageId' => $messageId]);
        } else {
            $this->logger->error('Failed to report message', ['messageId' => $messageId, 'reason' => $reason]);
            return new ApiResponse(false, 'Failed to report message.', null, ['messageId' => $messageId]);
        }
    }

    /**
     * Sends a comment to a specific message.
     * Accepts POST requests with message ID and comment content.
     * Responds with success status and comment ID.
     *
     * @param int $messageId
     * @param string $guestName
     * @param string $content
     * @return ApiResponse
     * @throws Exception
     */
    public function sendComment(int $messageId, string $guestName,string $content): ApiResponse
    {
        $this->logger->info('Guest attempted to send a comment', ['messageId' => $messageId, 'guestName' => $guestName, 'content' => $content]);
        // Sanitize and validate input
        $content = InputValidator::sanitizeString($content);
        $guestName = InputValidator::sanitizeString($guestName);
        if (!InputValidator::isNotEmpty($content)) {
            $this->logger->warning('there is no message attached dumb-ass', ['messageId' => $messageId, 'guestName' => $guestName, 'content' => $content]);
            return new ApiResponse(false, 'Comment content cannot be empty.', null, ['messageId' => $messageId]);
        }

        if (!InputValidator::isValidInteger($messageId)) {
            return new ApiResponse(false, 'Invalid message ID.', null, ['messageId' => $messageId]);
        }

        // Check if the message exists
        $message = $this->commentRepository->addComment($messageId, $guestName, $content);
        if (!$message) {
            return new ApiResponse(false, 'Message not found.', null, ['messageId' => $messageId]);
        }

        $result = $this->commentRepository->addComment($messageId, $guestName, $content);
        if ($result) {
            $this->logger->info('Comment successfully sent', ['messageId' => $messageId, 'guestName' => $guestName, 'content' => $content]);
            return new ApiResponse(true, 'Comment sent successfully.', ['commentId' => $messageId, 'guestName' => $guestName, 'content' => $content]);
        } else {
            $this->logger->error('failed to send comment', ['messageId' => $messageId, 'guestName' => $guestName, 'content' => $content]);
            return new ApiResponse(false, 'Failed to send comment.',null, ['messageId' => $messageId, 'guestName' => $guestName, 'content' => $content]);
        }
    }

/*
    /**
     * Retrieves comments for a specific message.
     * Accepts GET requests with message ID as a parameter.
     * Responds with success status and comment data.
     *
     * @param int $messageId
     * @return ApiResponse
     * @throws Exception
     */
    /* not inn use at the moment:!
    public function getCommentsByMessageId(int $messageId): ApiResponse
    {
        // Validate messageId
        if (!InputValidator::isValidInteger($messageId)) {
            return new ApiResponse(false, 'Invalid message ID.', null, ['messageId' => $messageId]);
        }

        $comments = $this->commentRepository->getCommentsByMessageId($messageId);
        if (!$comments) {
            return new ApiResponse(false, 'Failed to retrieve comments.', null, ['messageId' => $messageId]);
        }

        return new ApiResponse(true, 'Comments retrieved successfully.', $comments);
    }*/
/*
    /** Moved to LecturerService
     * Used in LecturerController to reply to a student's message
     * Used in LecturerController
     * @param string $messageId
     * @param string $reply
     * @return ApiResponse
     * @throws Exception
     */
/*
    public function replyToMessage(string $messageId, string $reply): ApiResponse
    {
        $this->logger->info('attempting to send a lecturer reply', ['messageId' => $messageId, 'reply' => $reply]);
        //validate messageId
        if(!InputValidator::isValidInteger($messageId)) {
            $this->logger->warning('Invalid message ID.', ['messageId' => $messageId]);
            return new ApiResponse(false, 'Invalid message ID.', null, ['messageId' => $messageId]);
        }

        //Sanitize the reply
        $this->logger->info('Attempting to sanitize reply', ['messageId' => $messageId, 'reply' => $reply]);
        $sanitizedReply = InputValidator::sanitizeString($reply);

        if (!InputValidator::sanitizeString($sanitizedReply)) {
            $this->logger->warning('Invalid reply, man-dude-man, dude...', ['messageId' => $messageId, 'reply' => $reply]);
            return new ApiResponse(false, 'Well shit, something went wrong?', null, ['messageId' => $messageId]);
        }
        //check it the input is empty
        if (!InputValidator::isNotEmpty($sanitizedReply)) {
            $this->logger->warning('Some how the empty message got this far, wow.', ['messageId' => $messageId, 'reply' => $reply]);
            return new ApiResponse(false, 'Message content cannot be empty.', ['messageId' => $messageId]);
        }

        $success = $this->lecturerRepository->replyToMessage($messageId, $sanitizedReply);

        if(!$success) {
            $this->logger->error('Well, that doesnt work does it?', ['messageId' => $messageId, 'reply' => $reply]);
            return new ApiResponse(false, 'Failed to send reply.', ['messageId' => $messageId]);
        }

        $this->logger->info('Reply successfully sent, oh mama!', ['messageId' => $messageId, 'reply' => $reply]);
        return new ApiResponse(true, 'Reply sent successfully.', ['messageId' => $messageId]);
    }*/
}