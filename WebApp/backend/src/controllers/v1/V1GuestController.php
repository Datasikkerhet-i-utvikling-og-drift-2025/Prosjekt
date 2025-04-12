<?php

namespace controllers\v1;

use helpers\ApiHelper;
use helpers\ApiResponse;
use managers\SessionManager;
use services\MessageService;
use services\GuestService;
use JsonException;
use Exception;

/**Class V1GuestController
 * Provides endpoints for guest users to interact with the system.
 * This includes getting messages from a course, reporting messages, and sending comments.
 */

class V1GuestController
{
    private GuestService $guestService;

    /**
     * V1GuestController constructor.
     *
     * @param MessageService $messageService
     * @param SessionManager $sessionManager
     * @param GuestService $guestService
     */
    public function __construct(GuestService $guestService)
    {
        $this->guestService = $guestService;
    }
    /**
     * Retrieves messages from a specific course.
     * Accepts POST requests with course ID as a parameter.
     * Responds with success status and message data.
     *
     * @return void
     * @throws JsonException
     */

     public function authorizePin(): void
    {
        ApiHelper::requirePost();

        try {
            $pin = $_POST['pin'] ?? null;

            if (!$pin) {
                ApiHelper::sendError(400, 'PIN is required.');
                return; // Stop here if there's an error
            }

            $course = $this->guestService->getCourseByPin($pin);

            if ($course) {
                // Authorization successful
                //$response = new ApiResponse(true, 'Authorization successful.', ['course' => $course]);
                //ApiHelper::sendApiResponse(200, $response);

                // If it's a web page request and not just an API call, you might redirect here:
                $_SESSION['authorized_courses'][$course['id']] = true;
                header('Location: /guests/dashboard?course_id=' . $course['id']);
                exit;
            } else {
                // Invalid PIN
                ApiHelper::sendError(403, 'Invalid PIN.');
                return; // Stop here if there's an error
            }

        } catch (JsonException $e) {
            ApiHelper::sendError(400, 'Invalid JSON.', ['exception' => $e->getMessage()]);
        } catch (Exception $e) {
            ApiHelper::sendError(500, 'Server error.', ['exception' => $e->getMessage()]);
        }
    }

    
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

            $response = $this->guestService->getMessagesByCourseId($courseId);

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

            $response = $this->guestService->reportMessage((int)$messageId, $reason);
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
            $guestName = $input['guestName'] ?? null;
            $messageId = $input['messageId'] ?? null;
            $content = $input['content'] ?? null;

            if (!$messageId || !$content) {
                ApiHelper::sendError(400, 'Message ID and content are required.');
                return;
            }

            $response = $this->guestService->sendComment((int)$messageId, $guestName, $content);
            ApiHelper::sendApiResponse($response->success ? 200 : 400, $response);
        } catch (Exception $e) {
            ApiHelper::sendError(500, 'Internal server error.', ['exception' => $e->getMessage()]);
        }

    }


}