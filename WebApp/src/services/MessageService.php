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

    /**
     * MessageService constructor.
     *
     * @param MessageRepository $messageRepository
     * @param CommentRepository $commentRepository
    // * @param CourseRepository $courseRepository
     * @param LecturerRepository $lecturerRepository
     */
    public function __construct(
        MessageRepository $messageRepository,
        CommentRepository $commentRepository,
        //CourseRepository $courseRepository,
        LecturerRepository $lecturerRepository
    ) {
        $this->messageRepository = $messageRepository;
        $this->commentRepository = $commentRepository;
        $this->lecturerRepository = $lecturerRepository;
        //$this->courseRepository = $courseRepository;
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
     * @throws Exception
     */
    public function getMessagesByCourse(int $courseId): ApiResponse
    {
        // Validate courseID
        if (!InputValidator::isValidInteger($courseId)) {
            return new ApiResponse(false, 'Invalid course ID.', null, ['courseId' => $courseId]);
        }

        $messages = $this->messageRepository->getMessagesByCourse($courseId);
        if (!$messages) {
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
        $result = $this->messageRepository->reportMessageById($messageId, $reason);
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
     * @param string $guestName
     * @param string $content
     * @return ApiResponse
     * @throws Exception
     */
    public function sendComment(int $messageId, string $guestName,string $content): ApiResponse
    {
        // Sanitize and validate input
        $content = InputValidator::sanitizeString($content);
        $guestName = InputValidator::sanitizeString($guestName);
        if (!InputValidator::isNotEmpty($content)) {
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
            return new ApiResponse(true, 'Comment sent successfully.', ['commentId' => $messageId]);
        } else {
            return new ApiResponse(false, 'Failed to send comment.',null, ['messageId' => $messageId]);
        }
    }


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

    /**
     * reply to a student's message
     * Used in LecturerController
     * @param string $messageId
     * @param string $reply
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

    public function getLecturerInfo(int $lecturerId): ApiResponse
{
    // Validate lecturerId
    if (!InputValidator::isValidInteger($lecturerId)) {
        return new ApiResponse(false, 'Invalid lecturer ID.', null, ['lecturerId' => $lecturerId]);
    }

    try {
        // Fetch lecturer details from the repository
        $lecturer = $this->lecturerRepository->fetchLecturerById($lecturerId);

        if ($lecturer) {
            return new ApiResponse(true, 'Lecturer retrieved successfully.', $lecturer);
        } else {
            return new ApiResponse(false, 'Lecturer not found.', null, ['lecturerId' => $lecturerId]);
        }
    } catch (Exception $e) {
        // Handle unexpected errors
        return new ApiResponse(false, 'An error occurred while retrieving the lecturer.', null, ['error' => $e->getMessage()]);
    }
}


}