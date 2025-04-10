<?php

namespace controllers\v1;

use helpers\ApiHelper;
use helpers\ApiResponse;
use managers\SessionManager;
use services\MessageService;
use JsonException;
use Exception;

/**Class V1GuestController
 * Provides endpoints for guest users to interact with the system.
 * This includes getting messages from a course, reporting messages, and sending comments.
 */

class V1GuestController
{
    private MessageService $messageService;
    /**
     * V1GuestController constructor.
     *
     * @param MessageService $messageService
     * @param SessionManager $sessionManager
     */
    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }
    /**
     * Retrieves messages from a specific course.
     * Accepts POST requests with course ID as a parameter.
     * Responds with success status and message data.
     *
     * @return void
     * @throws JsonException
     */
    
    public function getMessagesByCourse()
    {
        ApiHelper::requirePost();
        //ApiHelper::requireApiToken();

        try {
            $input = ApiHelper::getJsonInput();
            $courseId = ApiHelper::getJsonInput()['courseId'] ?? null;
            if (!$courseId) {
                ApiHelper::sendError(400, 'course ID is required.');
                return;
            }

            $response = $this->messageService->getMessagesByCourse($courseId);

            ApiHelper::sendApiResponse($response->success ? 200 : 400, $response);
        } catch (JsonException $e) {
            ApiHelper::sendError(400, 'Invalid JSON input.', ['exception' => $e->getMessage()]);
        } catch (Exception $e) {
            ApiHelper::sendError(500, 'Internal server error.', ['exception' => $e->getMessage()]);
        }
    

    }


    public function reportMessage()
    {
        ApiHelper::requirePost();

        try {
            $input = ApiHelper::getJsonInput();
            $messageId = $input['messageId'] ?? null;
            $reason = $input['reason'] ?? null;

            if (!$messageId || !$reason) {
                ApiHelper::sendError(400, 'Message ID and reason are required.');
                return;
            }

            $response = $this->messageService->reportMessage((int)$messageId, $reason);
            ApiHelper::sendApiResponse($response->success ? 200 : 400, $response);
        } catch (Exception $e) {
            ApiHelper::sendError(500, 'Internal server error.', ['exception' => $e->getMessage()]);
        }

    }


    public function sendComment()
    {
        ApiHelper::requirePost();

        try {
            $input = ApiHelper::getJsonInput();
            $messageId = $input['messageId'] ?? null;
            $content = $input['content'] ?? null;

            if (!$messageId || !$content) {
                ApiHelper::sendError(400, 'Message ID and content are required.');
                return;
            }

            $response = $this->messageService->sendComment((int)$messageId, $content);
            ApiHelper::sendApiResponse($response->success ? 200 : 400, $response);
        } catch (Exception $e) {
            ApiHelper::sendError(500, 'Internal server error.', ['exception' => $e->getMessage()]);
        }

    }


}