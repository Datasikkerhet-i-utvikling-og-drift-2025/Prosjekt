<?php

namespace services;

use Exception;
use helpers\ApiResponse;
use helpers\Logger;
use models\Course;
use repositories\CourseRepository;
use helpers\InputValidator;

class CourseService
{
    private CourseRepository $courseRepository;

    /**
     * @param CourseRepository $courseRepository
     */
    public function __construct(
        CourseRepository $courseRepository
    ){
        $this->courseRepository = $courseRepository;
    }





    /**
     * Retrieve the course by id, find use of getCourseByID()
     *
     * @param int $courseId
     * @return ApiResponse the Api response
     * @throws Exception
     */
    public function getCourseById(int $courseId): ApiResponse
    {
        //usikker om dette er riktig bruk av isValidInteger
        if (!InputValidator::isValidInteger($courseId)) {
            return new ApiResponse(false, 'Course id is empty??? well fuck me...', null, ['courseId' => $courseId]);
        }

        $input = $this->courseRepository->getCourseById($courseId);
        if (!$input) {
            return new ApiResponse(false, 'Course not found', null, ['courseId' => $courseId]);
        }
        return new ApiResponse(true, 'Course id retieved successfully', null, ['courseId' => $courseId]);
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
        $input = $this->courseRepository->getCourseByPinCode($pinCode);
        if (!$input) {
            return new ApiResponse(false, 'Course not found', null, ['courseId' => $pinCode]);
        }
        return new ApiResponse(true, 'Course pin retrieved successfully', null, ['courseId' => $pinCode]);
    }


    /**
     * A lecturer should be able to create a new course
     * @param array $courseData
     * @return ApiResponse
     * @throws Exception
     *
     */
     /*
    public function createCourse(array $courseData): ApiResponse
    {

        Logger::info('Creation method called with input: ' . json_encode($courseData, JSON_THROW_ON_ERROR));

        $validation = InputValidator::validateCourseCreation($courseData);
        Logger::debug('Validation result: ' . json_encode($validation, JSON_THROW_ON_ERROR));

        if ($validation['errors']) {
            Logger::warning('Validation failed.' . json_encode($validation['errors'], JSON_THROW_ON_ERROR));
            return new ApiResponse(false, 'Validation failed,', null , $validation['errors']);
        }

        $data = $validation['sanitized'];

        if ($this->courseRepository->createCourse($data['pinCode'])) {
            Logger::warning('PinCode already inn use: ' . $data['pinCode']);
            return new ApiResponse(false, 'PinCode already inn use: ' . $data['pinCode']);
        }

        Logger::info('PinCode is not i use, proceeding to create course');
        $course = new Course($data);
        Logger::debug('Course created: ' . json_encode($course, JSON_THROW_ON_ERROR));

        $success = $this->courseRepository->createCourse($course);
        if (!$success) {
            Logger::error('Failed to save course to database.');
            return new ApiResponse(false, 'Failed to save course to database.');
        }
        // trenger en måte å finne den innloggede lærerens lecturer_id sånn at det nye opprettede kurset
        // blir lagret på læreren som registrerer nytt kurs...
        // user
        // Logger::success('Course saved to database: ' . $user->lecturerId);

        //hvis brukeren har role === 'lecturer'
        // Så logg info om at kurs er i ferd med å opprettes

    }*/

}
