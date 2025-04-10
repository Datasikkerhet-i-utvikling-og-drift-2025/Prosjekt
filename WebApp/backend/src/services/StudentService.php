<?php

namespace services;

use DateMalformedStringException;
use Exception;
use Helpers\ApiResponse;
use JsonException;
use models\Message;
use repositories\StudentRepository;
use helpers\InputValidator;


class StudentService
{
    private StudentRepository $studentRepository;
    private InputValidator $inputValidator;

    /**
     * @param StudentRepository $studentRepository
     * @param InputValidator $inputvalidator
     */
    public function __construct(
        StudentRepository $studentRepository
    ){
        $this->studentRepository = $studentRepository;
    }

    /**
     * function for students to send a message to a specific course
     *
     * @param string $studentId
     * @param string $courseId
     * @param ?string $anonymousId
     * @param string $content
     * @return ApiResponse
     * @throws DateMalformedStringException
     * @throws Exception
     */
    public function sendMessage(string $studentId, string $courseId, ?string $anonymousId, string $content): ApiResponse
    {
        //sanitize and validate input
        $studentId = InputValidator::isValidInteger($studentId);
        $courseId = InputValidator::isValidInteger($courseId);
        $anonymousId = InputValidator::isValidInteger($anonymousId);
        $content = InputValidator::sanitizeString($content);

        if(!InputValidator::isNotEmpty($content)) {
            return new ApiResponse(false, 'Message cannot be empty! Herro? liek whut');
        }
        if(!InputValidator::isNotEmpty($studentId)) {
            return new ApiResponse(false, 'This for student function only >:I ');
        }
        if(!InputValidator::isNotEmpty($courseId)) {
            return new ApiResponse(false, 'Nah man, where dah course be, yeh written mon, ba boa dem.');
        }
        $input = $this->inputValidator->validateMessage( [
            'studentId' => $studentId,
            'courseId' => $courseId,
            'anonymousId' => $anonymousId,
            'content' => $content
        ]);

        $data = $input['sanitized'];
        if (!$input) {
            return new ApiResponse(false, 'Message cannot be empty!');
        }
        $message = new Message($data);

        $success = $this->studentRepository->sendMessage($studentId, $courseId, $anonymousId, $content);

        if (!$success) {
            return new ApiResponse(false, 'Message cannot be empty!');
        }

        return new ApiResponse(true, 'Message sent sucessfully!', $message);
    }

    //Variable will allways be true on line 93...
    /**
     * @param string $studentId
     * @return ApiResponse
     * @throws JsonException
     */

    public function getMessagesByStudent(string $studentId): ApiResponse
    {
        $studentId = InputValidator::sanitizeString($studentId);
        if (!$studentId = InputValidator::isValidInteger($studentId)){
            return new ApiResponse(false, 'Student id is invalid!', null, ['studentId' => $studentId]);
        }

        $messages = $this->studentRepository->getMessagesByStudent($studentId);
        if (empty($messages)) {
            return new ApiResponse(false, 'Student id is invalid!', null, ['studentId' => $studentId]);
        }

        return new ApiResponse(true, 'Messages found!', $messages);

    }

    //not complete
    /**
     * @param string $messageId
     * @param string $studentId
     * @return ApiResponse
     * @throws JsonException
     */
    public function getMessagesWithReply(string $messageId, string $studentId): ApiResponse
    {
        $messageId = InputValidator::sanitizeString($messageId);
        $studentId = InputValidator::sanitizeString($studentId);
        if (!$messageId = InputValidator::isValidInteger($messageId)){
            return new ApiResponse(false, 'Message id is invalid!', null, ['messageId' => $messageId]);
        }
        if (!$studentId = InputValidator::isValidInteger($studentId)){
            return new ApiResponse(false, 'Student id is invalid!', null, ['studentId' => $studentId]);
        }

        $messages = $this->studentRepository->getMessageWithReply($messageId, $studentId);

        if (empty($messages)) {
            return new ApiResponse(false, 'Message id not found', null, ['messageId' => $messageId]);
        }
        return new ApiResponse(true, 'Messages found!', $messages);
    }

    /**
     * @return ApiResponse
     * @throws JsonException
     */
    public function getAvailableCourses(): ApiResponse
    {
        $courses = $this->studentRepository->getAvailableCourses();
        if (empty($courses)) {
            return new ApiResponse(false, 'No available courses at the moment!', null, ['courses' => $courses]);
        }
        return new ApiResponse(true, 'Available courses found!', $courses);
    }


}