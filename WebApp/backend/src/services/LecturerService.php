<?php

namespace services;

use Exception;
use helpers\GrayLogger;
use helpers\InputValidator;
use Helpers\ApiResponse;
use repositories\LecturerRepository;
use JsonException;

/** Class LecturerService
 *
 */

class LecturerService
{
    private LecturerRepository $lecturerRepository;

    private GrayLogger $logger;

    public function __construct(LecturerRepository $lecturerRepository)
    {
        $this->lecturerRepository = $lecturerRepository;
        $this->logger = GrayLogger::getInstance();
    }

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


    /**
     * Used in LecturerController to reply to a student's message
     * Used in LecturerController
     * @param string $messageId
     * @param string $reply
     * @return ApiResponse
     * @throws Exception
     */

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
    }

    /**
     * @param int $messageId
     * @return ApiResponse
     * @throws JsonException
     */

    public function getMessageById(int $messageId): ApiResponse
    {
        $this->logger->info('Getting message by ID', ['messageId' => $messageId]);

        $validation = InputValidator::isValidInteger($messageId);
        $this->logger->info('Attempting to get message by ID', ['messageId' => $messageId]);


        $message = $this->lecturerRepository->getMessageById($validation);
        if(!$message) {
            $this->logger->error('Failed to retrieve message', ['messageId' => $messageId]);
            return new ApiResponse(false, 'Failed to retrieve message.', null, ['messageId' => $messageId]);
        }

        $this->logger->info('Message retrieved successfully', ['messageId' => $messageId]);
        return new ApiResponse(true, 'Message retrieved successfully.', $message);
    }


}