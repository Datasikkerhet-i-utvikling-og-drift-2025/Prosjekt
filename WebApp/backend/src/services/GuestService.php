<?php

namespace services;

use Exception;
Use helpers\ApiResponse;
use helpers\InputValidator;
use repositories\GuestRepository;
use helpers\GrayLogger;

class GuestService
{
    private GuestRepository $guestRepository;
    private Graylogger $logger;

    /**
     * @param GuestRepository $guestRepository
     */
    public function __construct(
        GuestRepository $guestRepository
    ){
        $this->guestRepository = $guestRepository;
        $this->logger = GrayLogger::getInstance();
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
        $message = $this->guestRepository->addComment($messageId, $guestName, $content);
        if (!$message) {
            return new ApiResponse(false, 'Message not found.', null, ['messageId' => $messageId]);
        }

        $result = $this->guestRepository->addComment($messageId, $guestName, $content);
        if ($result) {
            $this->logger->info('Comment successfully sent', ['messageId' => $messageId, 'guestName' => $guestName, 'content' => $content]);
            return new ApiResponse(true, 'Comment sent successfully.', ['commentId' => $messageId, 'guestName' => $guestName, 'content' => $content]);
        } else {
            $this->logger->error('failed to send comment', ['messageId' => $messageId, 'guestName' => $guestName, 'content' => $content]);
            return new ApiResponse(false, 'Failed to send comment.',null, ['messageId' => $messageId, 'guestName' => $guestName, 'content' => $content]);
        }
    }

    /**
     * @param string $pinCode
     * @return ApiResponse
     * @throws Exception
     */
    public function getCourseByPin(string $pinCode): ApiResponse
    {
        //hmmm har Inputvalidator en saniteringsfunksjon for pincode?
        if (!InputValidator::isValidInteger($pinCode)) {
            return new ApiResponse(false, 'Course id is empty??? well fuck me...', null, ['courseId' => $pinCode]);
        }
        //getCourseByPin er nokk ikke laget enda
        $input = $this->guestRepository->getCourseByPinCode($pinCode);
        if (!$input) {
            return new ApiResponse(false, 'Course not found', null, ['courseId' => $pinCode]);
        }
        return new ApiResponse(true, 'Course pin retrieved successfully', null, ['courseId' => $pinCode]);
    }

    /** Er ikke helt ferdig med denne
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
        $message = $this->guestRepository->getMessageById($messageId);
        if (!$message) {
            $this->logger->error('Failed to retrieve message', ['messageId' => $messageId]);
            return new ApiResponse(false, 'Message not found.', null, ['messageId' => $messageId]);
        }

        // Create the report
        $result = $this->guestRepository->reportMessageById($messageId, $reason);
        if ($result) {
            $this->logger->info('Reported message successfully', ['messageId' => $messageId, 'reason' => $reason]);
            return new ApiResponse(true, 'Message reported successfully.', ['messageId' => $messageId]);
        } else {
            $this->logger->error('Failed to report message', ['messageId' => $messageId, 'reason' => $reason]);
            return new ApiResponse(false, 'Failed to report message.', null, ['messageId' => $messageId]);
        }
    }
}