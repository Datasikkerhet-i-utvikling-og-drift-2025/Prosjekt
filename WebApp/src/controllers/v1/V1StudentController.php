<?php

namespace controllers\v1;

use helpers\ApiHelper;
use helpers\ApiResponse;
use managers\SessionManager;
use services\MessageService;
use JsonException;
use Exception;

class V1StudentController
{
    private MessageService $messageService;

    /**
     *V1StudentController constructor.
     *
     *@param MessageService $messageService
     *
     */

    public function __construct(MessageService $messageService)
    {
       $this->messageService = $messageService;
    }

    //controller function for student users to interact with the system.
    // This includes sending messages, getting messages from a subject, reporting messages, and sending comments.
    // sendMessage -> messageService -> sendMessage -> Message?
    /**
     * @return void
     * @throws JsonException
     */
    public function sendMessage(): void
    {
        ApiHelper::requirePost();
        ApiHelper::requireApiToken();

        try {
           $contentType = $_SERVER["CONTENT_TYPE"] ?? '';

           if ($contentType === "application/json") {
               $input = ApiHelper::getJsonInput();
           } else {
               $input = $_POST;
           }

            $response = $this->messageService->sendMessage($input);

            ApiHelper::sendApiResponse($response->success ? 200 : 400, $response);
        } catch (JsonException $e) {
            ApiHelper::sendError(400, 'Invalid JSON input.', ['exception' => $e->getMessage()]);
        } catch (Exception $e) {
           ApiHelper::sendError(500, 'Internal server error.', ['exception' => $e->getMessage()]);
        }

    }

    /*
    public function getMessagesByCourse()
    {
        ApiHelper::requirePost();
        //ApiHelper::requireApiToken();

        try {
            $input = ApiHelper::getJsonInput();
            $courseId = ApiHelper::getJsonInput()['courseId'] ?? null;
            if (!$courseId) {
                ApiHelper::sendError(400, 'course ID is required.');
            }

            $response = $this->messageService->getMessagesByCourse($courseId);

            ApiHelper::sendApiResponse($response->success ? 200 : 400, $response);
        } catch (JsonException $e) {
            ApiHelper::sendError(400, 'Invalid JSON input.', ['exception' => $e->getMessage()]);
        } catch (Exception $e) {
            ApiHelper::sendError(500, 'Internal server error.', ['exception' => $e->getMessage()]);
        }

    }*/


}