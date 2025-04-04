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
    //controller function for student users to interact with the system.
    // This includes sending messages, getting messages from a subject, reporting messages, and sending comments.
    public function sendMessage()
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

            $response = $this->messageService->sendMessage($courseId);

            ApiHelper::sendApiResponse($response->success ? 200 : 400, $response);
        } catch (JsonException $e) {
            ApiHelper::sendError(400, 'Invalid JSON input.', ['exception' => $e->getMessage()]);
        } catch (Exception $e) {
           ApiHelper::sendError(500, 'Internal server error.', ['exception' => $e->getMessage()]);
        }

    }


    public function getMessages()
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

            $response = $this->messageService->getMessagesFromCourse($courseId);

            ApiHelper::sendApiResponse($response->success ? 200 : 400, $response);
        } catch (JsonException $e) {
            ApiHelper::sendError(400, 'Invalid JSON input.', ['exception' => $e->getMessage()]);
        } catch (Exception $e) {
            ApiHelper::sendError(500, 'Internal server error.', ['exception' => $e->getMessage()]);
        }

    }


}