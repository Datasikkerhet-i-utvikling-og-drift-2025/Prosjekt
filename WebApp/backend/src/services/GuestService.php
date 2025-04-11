<?php

namespace services;

use helpers\InputValidator;
use helpers\Logger;
use repositories\CourseRepository;

class GuestService
{
    private CourseRepository $courseRepository;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    /**
     * Authorize a course by its pin.
     *
     * @param string $pin The pin of the course to authorize.
     * @return array|null The course data if found, null otherwise.
     */
    public function authorizeCourseByPin(string $pin): ?array
    {
        // Validate the pin
        if (!InputValidator::isNotEmpty($pin)) {
            Logger::error("Authorization failed: Pin cannot be empty.");
            return null;
        }

        // Attempt to find the course by pin
        $course = $this->courseRepository->findCourseByPin($pin);

        if (!$course) {
            Logger::warning("Authorization failed: No course found for pin: $pin");
            return null;
        }

        Logger::success("Course authorized successfully for pin: $pin");
        return $course;
    }
}