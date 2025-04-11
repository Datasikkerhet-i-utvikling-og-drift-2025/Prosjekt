<?php

namespace services;

use DateMalformedStringException;
use Exception;
use finfo;
use helpers\AuthHelper;
use helpers\InputValidator;
use helpers\ApiResponse;
use helpers\GrayLogger;
use managers\JWTManager;
use models\Course;
use repositories\UserRepository;
use repositories\LecturerRepository;
use factories\UserFactory;
use models\Lecturer;
use RuntimeException;
use Random\RandomException;
use Throwable;

/**
 * Class AuthService
 * Handles authentication and registration logic.
 * Compatible with both web and mobile clients.
 */
class AuthService
{
    private UserRepository $userRepository;
    private LecturerRepository $lecturerRepository;
    private JWTManager $jwtManager;
    private GrayLogger $logger;

    /**
     * AuthService constructor.
     *
     * @param UserRepository $userRepository
     * @param CourseRepository $courseRepository
     * @param JWTManager $jwtManager
     */
    public function __construct(UserRepository $userRepository, LecturerRepository $lecturerRepository, JWTManager $jwtManager)
    {
        $this->userRepository = $userRepository;
        $this->lecturerRepository = $lecturerRepository;
        $this->jwtManager = $jwtManager;
        $this->logger = GrayLogger::getInstance();
    }

    /**
     * Registers a new user.
     *
     * @param array $userData
     * @return ApiResponse
     * @throws RandomException|DateMalformedStringException|Exception
     */
    public function register(array $userData): ApiResponse
    {
        $this->logger->info('Register method called', ['payload' => $userData]);

        $validation = InputValidator::validateRegistration($userData);
        $this->logger->debug('Validation result', $validation);

        if (!empty($validation['errors'])) {
            $this->logger->warning('Validation failed', ['errors' => $validation['errors']]);
            return new ApiResponse(false, 'Validation failed.', null, $validation['errors']);
        }

        $data = $validation['sanitized'];

        if ($this->userRepository->getUserByEmail($data['email'])) {
            $this->logger->warning('Email already registered', ['email' => $data['email']]);
            return new ApiResponse(false, 'Email already registered.');
        }

        $this->logger->info('Hashing password and processing image upload...');
        $data['password'] = AuthHelper::hashPassword($data['password']);
        $data['imagePath'] = $this->handleProfilePictureUpload();

        $user = UserFactory::createUser($data);
        $this->logger->debug('User object created', ['user' => $user->toArray()]);

        if (!$this->userRepository->createUser($user)) {
            $this->logger->error('Failed to save user to database.');
            return new ApiResponse(false, 'Registration failed.');
        }

        $this->logger->info('User saved to database', ['email' => $user->email]);

        if ($user->role->value === 'lecturer') {
            $this->logger->info('Creating course for lecturer...');
            try {
                $lecturer = $this->userRepository->getUserByEmail($data['email']);
                $data['lecturerId'] = $lecturer->id ?? null;
                $data['pinCode'] = $data['coursePin'];
                $course = new Course($data);

                if (!$this->lecturerRepository->createCourse($course)) {
                    $this->logger->error('Failed to create course.');
                    return new ApiResponse(false, 'Registration succeeded, but course creation failed.');
                }

                $this->logger->info('Course created successfully.', ['courseCode' => $data['courseCode']]);
            } catch (Throwable $e) {
                $this->logger->error('Exception during course creation.', ['exception' => $e->getMessage()]);
                return new ApiResponse(false, 'Course creation failed.', null, ['exception' => $e->getMessage()]);
            }
        }

        $token = $this->jwtManager->generateToken([
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->role->value
        ]);

        $userArray = $user->toArray();
        $userArray['token'] = $token;

        $this->logger->info('Registration successful', ['user' => $userArray]);
        return new ApiResponse(true, 'Registration successful.', $userArray);
    }

    /**
     * Authenticates a user and issues JWT.
     *
     * @param array $credentials
     * @return ApiResponse
     * @throws DateMalformedStringException|Exception
     */
    public function login(array $credentials): ApiResponse
    {
        $this->logger->info('Login attempt', ['email' => $credentials['email'] ?? null]);

        if (empty($credentials['email']) || empty($credentials['password'])) {
            return new ApiResponse(false, 'Email and password required.');
        }

        $user = $this->userRepository->getUserByEmail($credentials['email']);
        if (!$user || !AuthHelper::verifyPassword($credentials['password'], $user->password)) {
            $this->logger->warning('Invalid login credentials', ['email' => $credentials['email']]);
            return new ApiResponse(false, 'Invalid credentials.');
        }

        $token = $this->jwtManager->generateToken([
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->role->value
        ]);

        $userArray = $user->toArray();
        $userArray['token'] = $token;

        $this->logger->info('Login successful', ['user' => $userArray]);

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
