<?php

namespace controllers\v1;

use helpers\ApiHelper;
use services\MessageService;
use JsonException;
use Exception;

class V1LecturerController
{
    private MessageService $messageService;

    public function registerSubject()
    {

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

            $response = $this->messageService->getMessagesByCourse((int)$courseId); // (int) casting is safer here

            ApiHelper::sendApiResponse($response->success ? 200 : 400, $response);

        } catch (JsonException $e) {
            ApiHelper::sendError(400, 'Invalid JSON input.',  ['exception' => $e->getMessage()]);
        } catch (Exception $e) {
            ApiHelper::sendError(500, 'Internal server error.', ['exception' => $e->getMessage()]);
        }

    }

    /**
     * Uses from Message-> LecturerRepository -> MessageService -> LecturerRepository -> Controller
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
            $response = $this->messageService->replyToMessage($messageId, $reply);

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

}