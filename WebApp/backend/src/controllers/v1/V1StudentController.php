<?php

namespace controllers\v1;

use helpers\ApiHelper;
use helpers\ApiResponse;
use helpers\InputValidator;
use managers\SessionManager;
use services\StudentService;
use JsonException;
use Exception;

class V1StudentController
{
    private StudentService $studentService;

    /**
     *V1StudentController constructor.
     *
     *@param StudentService $studentService
     *
     */

    public function __construct(StudentService $studentService)
    {
       $this->studentService = $studentService;
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
        ApiHelper::requireApiKey();

        try {
           $postData = ApiHelper::getJsonInput();

           $studentId = $postData['studentId'] ?? null;
           $courseId = $postData['courseId'] ?? null;
           $isAnonymous = $postData['anonymousId'] ?? false;
           $content = $postData['content'] ?? null;

           $anonymousId = null;
           if ($isAnonymous) {
               $anonymousId = $postData['anonymousId'] ?? null;
           }

           $response = $this->studentService->sendMessage($studentId, $courseId, $anonymousId, $content);

            ApiHelper::sendApiResponse($response->success ? 200 : 400, $response);
        } catch (JsonException $e) {
            ApiHelper::sendError(400, 'Invalid JSON input.', ['exception' => $e->getMessage()]);
        } catch (Exception $e) {
           ApiHelper::sendError(500, 'Internal server error.', ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function getMessagesByStudent(): void
    {
        ApiHelper::requirePost();
        ApiHelper::requireApiKey();

        try {
            $input = ApiHelper::getInput();

            if (!$input) {
                ApiHelper::sendError(400, 'StudentId is required.', ['exception' => 'studentId']);
            }

           $response = $this->studentService->getMessagesByStudent($input['studentId']);

            ApiHelper::sendApiResponse($response->success ? 200 : 400, $response);
        } catch (JsonException $e) {
            ApiHelper::sendError(500, 'Internal server error.', ['exception' => $e->getMessage()]);
        }
    }

    public function getMessageWithReply(): void
    {
        ApiHelper::requirePost();
        ApiHelper::requireApiKey();

        try {
            $messageId = $_POST['messageId'] ?? null;
            $studentId = $_POST['studentId'] ?? null;
            if (!$messageId) {
                ApiHelper::sendError(400, 'MessageId is required.', ['exception' => 'messageId']);
            }
            if (!$studentId) {
                ApiHelper::sendError(400, 'StudentId is required.', ['exception' => 'studentId']);
            }

            $response = $this->studentService->getMessagesWithReply($studentId, $messageId);

            ApiHelper::sendApiResponse($response->success ? 200 : 400, $response);
        } catch (JsonException $e) {
            ApiHelper::sendError(500, 'Internal server error.', ['exception' => $e->getMessage()]);
        }

    }

    /**
     * @return void
     * @throws JsonException
     */
    public function getAvailableCourses(): void
    {
        ApiHelper::requireGet();
        ApiHelper::requireApiKey();

        try {
            $response = $this->studentService->getAvailableCourses();
            ApiHelper::sendApiResponse($response->success ? 200 : 400, $response);
        } catch (Exception $e) {
            ApiHelper::sendError(500, 'Internal server error.', ['exception' => $e->getMessage()]);
        }
    }

    /*
    /**
     * @return void
     * @throws JsonException
     */
    /*
 public function getMessagesByCourse(): void
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

 */


}