<?php

namespace services;

use DateMalformedStringException;
use factories\UserFactory;
use helpers\AuthHelper;
use helpers\InputValidator;
use helpers\Logger;
use helpers\ApiHelper;
use helpers\ApiResponse;
use Random\RandomException;
use repositories\UserRepository;
use RuntimeException;

class AuthService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Registers a new user.
     *
     * @param array $userData The registration data.
     * @return ApiResponse Returns response object with success status and message.
     * @throws DateMalformedStringException|RandomException
     */
    public function register(array $userData): ApiResponse
    {
        // Validate user input
        $validationResult = InputValidator::validateRegistration($userData);
        if (!empty($validationResult['errors'])) {
            return new ApiResponse(false, 'Invalid registration data.', null, $validationResult['errors']);
        }

        $sanitizedData = $validationResult['sanitized'];
        $sanitizedData['password'] = AuthHelper::hashPassword($sanitizedData['password']);
        $sanitizedData['image_path'] = $this->handleProfilePictureUpload();

        // Check if email already exists
        if ($this->userRepository->getUserByEmail($sanitizedData['email'])) {
            return new ApiResponse(false, 'Email is already registered.');
        }

        // Create user instance and save to database
        $user = UserFactory::createUser($sanitizedData);
        $created = $this->userRepository->createUser($user);

        if (!$created) {
            Logger::error("User registration failed for email: {$sanitizedData['email']}");
            return new ApiResponse(false, 'Registration failed due to an internal error.');
        }

        Logger::success("User registered successfully: {$sanitizedData['email']}");

        return new ApiResponse(true, 'Registration successful.', $user->toArray());
    }

    /**
     * Handles profile picture upload.
     *
     * @return string|null Returns the image path or null if no image is uploaded.
     * @throws RandomException
     */
    private function handleProfilePictureUpload(): ?string
    {
        if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $uploadDir = __DIR__ . '/../../public/uploads/profile_pictures/';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $uploadDir));
        }

        $fileExtension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $uniqueFileName = bin2hex(random_bytes(16)) . '.' . $fileExtension;
        $filePath = $uploadDir . $uniqueFileName;

        if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $filePath)) {
            Logger::error("Failed to move uploaded profile picture.");
            return null;
        }

        return '/uploads/profile_pictures/' . $uniqueFileName;
    }



    public function login()
    {

    }

    public function changePassword()
    {

    }

    public function forgotPassword()
    {

    }
}
