<?php

namespace controllers\v1;

use helpers\ApiHelper;
use managers\SessionManager;
use services\LecturerService;
use JsonException;
use Exception;

class V1LecturerController
{
    private LecturerService $lecturerService;
    public function __construct(LecturerService $lecturerService){
        $this->lecturerService = $lecturerService;
    }

    /**
     * @return void
     *
     * @throws JsonException
     */
    public function getMessages(): void
    {
        ApiHelper::requirePost();
        ApiHelper::requireApiToken(); // (optional security)

        try {
            $input = ApiHelper::getJsonInput(); // Get parsed JSON as array

            $courseId = $input['courseId'] ?? null; // <--- use $input you already fetched, not ApiHelper::getJsonInput() again

            if (!$courseId) {
                ApiHelper::sendError(400, 'Course ID is required.');
            }

            $response = $this->lecturerService->getMessagesForCourse((int)$courseId); // (int) casting is safer here

            ApiHelper::sendApiResponse($response->success ? 200 : 400, $response);

        } catch (JsonException $e) {
            ApiHelper::sendError(400, 'Invalid JSON input.',  ['exception' => $e->getMessage()]);
        } catch (Exception $e) {
            ApiHelper::sendError(500, 'Internal server error.', ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Uses from Message model-> LecturerRepository -> MessageService -> Controller
     * @return void
     * @throws JsonException
     */

    public function sendReply(): void
    {
        ApiHelper::requirePost();
        ApiHelper::requireApiToken();

        try {
            $input = ApiHelper::getJsonInput();
            //validate that input has messageId and replyContent
            if (!isset($input['messageId']) || !isset($input['replyContent'])) {
                ApiHelper::sendError(400, 'Missing required fields: messageId or replyContent.',
                    ['required' => ['messageId', 'replyContent'],
                        'received' => $input]);
            }

            $messageId = $input['messageId'];
            $reply = $input['replyContent'];
            $response = $this->lecturerService->replyToMessage($messageId, $reply);

            if ($response->success) {
                ApiHelper::sendApiResponse(200, $response);
            } else {
                ApiHelper::sendApiResponse(400, $response);
            }

        }catch (JsonException $e) {
            ApiHelper::sendError(400, 'Invalid JSON input.', ['error' => $e->getMessage()]  );
        } catch (Exception $e) {
            ApiHelper::sendError(500, 'Internal server error.', ['error' => $e->getMessage()]  );
        }
    }
    public function getLecturerDetails(): void
    {
        ApiHelper::requirePost();
        ApiHelper::requireApiToken();

    /**
     * @return void
     * @throws JsonException
     */

    public function getMessageById(): void
    {
        ApiHelper::requirePost();
        ApiHelper::requireApiToken();

        try {
            $input = ApiHelper::getJsonInput();

            $messageId = $input['messageId'] ?? null; // <--- use $input you already fetched, not ApiHelper::getJsonInput() again

            if (!$messageId) {
                ApiHelper::sendError(400, 'Message id is required.');
            }

            $response = $this->lecturerService->getMessageById($messageId);
            ApiHelper::sendApiResponse($response->success ? 200 : 400, $response);
        } catch (JsonException $e) {
            ApiHelper::sendError(400, 'Invalid JSON input.',  ['exception' => $e->getMessage()]);
        } catch (Exception $e) {
            ApiHelper::sendError(500, 'Internal server error.', ['exception' => $e->getMessage()]);
        }
    }

}