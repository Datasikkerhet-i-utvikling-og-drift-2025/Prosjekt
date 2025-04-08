<?php

namespace services;

use AllowDynamicProperties;
use DateMalformedStringException;
use Exception;
use finfo;
use helpers\AuthHelper;
use helpers\InputValidator;
use helpers\ApiResponse;
use helpers\Logger;
use managers\JWTManager;
use managers\SessionManager;
use models\Course;
use repositories\UserRepository;
use repositories\CourseRepository;
use factories\UserFactory;
use RuntimeException;
use Random\RandomException;

/**
 * Class AuthService
 * Handles authentication and registration logic.
 * Compatible with both web and mobile clients.
 * Uses session manager for secure session lifecycle handling.
 */
class AuthService
{
    private UserRepository $userRepository;
    private CourseRepository $courseRepository;
    private JWTManager $jwtManager;
    private SessionManager $sessionManager;

    /**
     * AuthService constructor.
     *
     * @param UserRepository $userRepository
     * @param CourseRepository $courseRepository
     * @param JWTManager $jwtManager
     * @param SessionManager $sessionManager
     */
    public function __construct(UserRepository $userRepository, CourseRepository $courseRepository, JWTManager $jwtManager, SessionManager $sessionManager)
    {
        $this->userRepository = $userRepository;
        $this->courseRepository = $courseRepository;
        $this->jwtManager = $jwtManager;
        $this->sessionManager = $sessionManager;
    }

    /**
     * Registers a new user.
     *
     * @param array $userData
     * @return ApiResponse
     * @throws RandomException|DateMalformedStringException
     * @throws Exception
     */
    public function register(array $userData): ApiResponse
    {
        Logger::info('Register method called with input: ' . json_encode($userData, JSON_THROW_ON_ERROR));

        $validation = InputValidator::validateRegistration($userData);
        Logger::debug('Validation result: ' . json_encode($validation, JSON_THROW_ON_ERROR));

        if (!empty($validation['errors'])) {
            Logger::warning('Validation failed.' . json_encode($validation['errors'], JSON_THROW_ON_ERROR));
            return new ApiResponse(false, 'Validation failed.', null, $validation['errors']);
        }

        $data = $validation['sanitized'];

        if ($this->userRepository->getUserByEmail($data['email'])) {
            Logger::warning('Email already registered: ' . $data['email']);
            return new ApiResponse(false, 'Email already registered.');
        }

        Logger::info('Email is not in use, proceeding to hash password.');
        $data['password'] = AuthHelper::hashPassword($data['password']);
        $data['imagePath'] = $this->handleProfilePictureUpload();
        Logger::info('Profile picture uploaded to: ' . $data['imagePath']);

        $user = UserFactory::createUser($data);
        Logger::debug('User object created: ' . json_encode($user->toArray(), JSON_THROW_ON_ERROR));

        $success = $this->userRepository->createUser($user);
        if (!$success) {
            Logger::error('Failed to save user to database.');
            return new ApiResponse(false, 'Registration failed.');
        }

        Logger::success('User saved to database: ' . $user->email);
// TODO fix this shit
        if ($user->role->value === 'lecturer') {
            Logger::info('User is lecturer, creating course.');
            $lecturer = $this->userRepository->getUserByEmail($data['email']);
            //$course = new Course();

            $courseCreated = $this->courseRepository->createCourse(
                $data['courseCode'],
                $data['courseName'],
                $lecturer?->id,
                $data['coursePin']
            );

            Logger::debug('createCourse() returned: ' . var_export($courseCreated, true));

            $courseCreated = $this->courseRepository->createCourse(
                $data['courseCode'],
                $data['courseName'],
                $lecturer?->id,
                $data['coursePin']
            );

            if (!$courseCreated) {
                Logger::error('Failed to create course for lecturer.');
                return new ApiResponse(false, 'Registration succeeded, but course creation failed.');
            }

            Logger::success('Course created for lecturer: ' . $data['courseCode']);
        }

        $token = $this->jwtManager->generateToken([
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->role->value
        ]);

        $userArray = $user->toArray();
        $userArray['token'] = $token;

        $this->sessionManager->storeUser($userArray, $token);

        // If the registered user is a Lecturer, create the course
        if ($user-> role == 'lecturer') {
            $course = $this->courseRepository->createCourse($data['code'], $data['name'], $user->id, $data['pinCode']);
            if (!$course) {
                return new ApiResponse(false, 'Course creation failed.');
            }
        }
        Logger::success('Registration successful for user: ' . $user->email);

        return new ApiResponse(true, 'Registration successful.', $userArray);


    }

    /**
     * Authenticates a user and issues JWT.
     *
     * @param array $credentials
     * @return ApiResponse
     * @throws DateMalformedStringException
     * @throws Exception
     */
    public function login(array $credentials): ApiResponse
    {
        if (empty($credentials['email']) || empty($credentials['password'])) {
            return new ApiResponse(false, 'Email and password required.');
        }

        $user = $this->userRepository->getUserByEmail($credentials['email']);
        if (!$user || !AuthHelper::verifyPassword($credentials['password'], $user->password)) {
            $this->sessionManager->incrementFailedLogin();
            return new ApiResponse(false, 'Invalid credentials.');
        }

        if ($this->sessionManager->tooManyFailedAttempts()) {
            return new ApiResponse(false, 'Too many failed login attempts. Try again later.');
        }

        $token = $this->jwtManager->generateToken([
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->role->value
        ]);

        $userArray = $user->toArray();
        $userArray['token'] = $token;

        $this->sessionManager->storeUser($userArray, $token);

        return new ApiResponse(true, 'Login successful.', $userArray);
    }

    /**
     * Handles secure upload of a profile picture.
     *
     * @return string|null
     * @throws RandomException
     */
    private function handleProfilePictureUpload(): ?string
    {
        if (!isset($_FILES['profilePicture']) || $_FILES['profilePicture']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $file = $_FILES['profilePicture'];
        $allowedTypes = ['image/jpeg', 'image/png'];
        $maxSize = 10 * 1024 * 1024;

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $allowedTypes, true)) {
            throw new RuntimeException('Invalid image format.');
        }

        if ($file['size'] > $maxSize) {
            throw new RuntimeException('Image exceeds maximum size.');
        }

        $ext = $mimeType === 'image/png' ? 'png' : 'jpg';
        $fileName = bin2hex(random_bytes(16)) . '.' . $ext;
        $uploadDir = __DIR__ . '/../../public/uploads/profile_pictures/';

        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $uploadDir));
        }

        $path = $uploadDir . $fileName;
        if (!move_uploaded_file($file['tmp_name'], $path)) {
            throw new RuntimeException('Failed to save uploaded file.');
        }

        return '/uploads/profile_pictures/' . $fileName;
    }
}
