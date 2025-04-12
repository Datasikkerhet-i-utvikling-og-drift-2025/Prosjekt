<?php

namespace services;

use DateMalformedStringException;
use Exception;
use Helpers\ApiResponse;
use JsonException;
use repositories\StudentRepository;
use helpers\InputValidator;
use helpers\GrayLogger;


class StudentService
{
    private StudentRepository $studentRepository;
    private GrayLogger $logger;


    /**
     * @param StudentRepository $studentRepository
     */
    public function __construct(
        StudentRepository $studentRepository
    ){
        $this->studentRepository = $studentRepository;
        $this->logger = GrayLogger::getInstance();
    }

    /**
     * function for students to send a message to a specific course
     *
     * @param string $studentId
     * @param string $courseId
     * @param ?string $anonymousId
     * @param string $content
     * @return ApiResponse
     * @throws Exception
     */
    public function sendMessage(string $studentId, string $courseId, ?string $anonymousId, string $content): ApiResponse
    {
        $this->logger->info('Students sendMessage function initiated. Params in use: ', ['studentId' => $studentId, 'courseId' => $courseId, 'anonymousId' => $anonymousId]);
        //sanitize and validate input
        $studentId = InputValidator::isValidInteger($studentId);
        $courseId = InputValidator::isValidInteger($courseId);
        $anonymousId = InputValidator::isValidInteger($anonymousId);
        $content = InputValidator::sanitizeString($content);

        if(!InputValidator::isNotEmpty($content)) {
            $this->logger->warning('did not contain any content, or is?', ['content' => $content]);
            return new ApiResponse(false, 'Message cannot be empty! Herro? liek whut');
        }
        if(!InputValidator::isNotEmpty($studentId)) {
            $this->logger->warning('did not contain any studentId, or is?', ['studentId' => $studentId]);
            return new ApiResponse(false, 'This for student function only >:I ');
        }
        if(!InputValidator::isNotEmpty($courseId)) {
            $this->logger->warning('did not contain any courseId, or is?', ['courseId' => $courseId]);
            return new ApiResponse(false, 'Nah man, where dah course be, yeh written mon, ba boa dem.');
        }
        /*
         * ser at jeg benytter meg av flere metoder for den samme tingen :/ kanskje ikke det smarteste med dobble meldinger...
        $this->logger->info('Params being validated and put in input array, oh, shit magie!',
            ['studentId' => $studentId, 'courseId' => $courseId, 'anonymousId' => $anonymousId, 'content' => $content]);

        $input = $this->inputValidator->validateMessage( [
            'studentId' => $studentId,
            'courseId' => $courseId,
            'anonymousId' => $anonymousId,
            'content' => $content
        ]);

        $this->logger->info('input has been sanitized and being put in data',
            ['studentId' => $studentId, 'courseId' => $courseId, 'anonymousId' => $anonymousId, 'content' => $content]);

        $data = $input['sanitized'];


        if (!$input) {
            $this->logger->debug('well shit, inputs now empty');
            return new ApiResponse(false, 'Message cannot be empty!');
        }

        $this->logger->info('new message initialized.', ['data' => $data]);
        $message = new Message($data);
        */

        $success = $this->studentRepository->sendMessage($studentId, $courseId, $anonymousId, $content);

        if (!empty($success)) {
            return new ApiResponse(false, 'Message cannot be empty!');
        }
        $this->logger->info('Now, we can devour the gods, togethaaa! -> Some God-Devouring Serpent.');
        return new ApiResponse(true, 'Message sent successfully!', $success);
    }

    //Variable will allways be true on line 93...
    /**
     * @param string $studentId
     * @return ApiResponse
     * @throws JsonException
     */

    public function getMessagesByStudent(int $studentId): ApiResponse
    {
        //hmm allways true when reached?
        if (!$studentId = InputValidator::isValidInteger($studentId)){
            $this->logger->warning('did not contain any studentId, or is?', ['studentId' => $studentId]);
            return new ApiResponse(false, 'Student id is invalid!', null, ['studentId' => $studentId]);
        }


        $messages = $this->studentRepository->getMessagesByStudent($studentId);
        if (empty($messages)) {
            return new ApiResponse(false, 'messages not found', null, ['studentId' => $studentId]);
        }
        $this->logger->info('Success', ['studentId' => $studentId, 'messages' => $messages]);
        return new ApiResponse(true, 'Messages found!', $messages);

    }

    //not complete
    /**
     * @param string $messageId
     * @param string $studentId
     * @return ApiResponse
     * @throws JsonException
     */
    public function getMessagesWithReply(int $messageId, int $studentId): ApiResponse
    {
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