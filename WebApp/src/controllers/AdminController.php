<?php

namespace controllers;

use helpers\ApiHelper;
use helpers\AuthHelper;
use helpers\Logger;
use JsonException;
use repositories\AdminRepository;
use repositories\UserRepository;
use repositories\MessageRepository;
use Exception;
use RuntimeException;
use service\DatabaseService;

class AdminController
{
    private AdminRepository $adminRepository;
    private UserRepository $userRepository;

    public function __construct(DatabaseService $db)
    {
        $this->adminRepository = new AdminRepository($db);
        $this->userRepository = new UserRepository($db);
    }

    /**
     * Get all users with optional role filtering
     * Method: GET /admin/users
     */
    public function getAllUsers()
    {
        AuthHelper::requireRole('admin');
        try {
            $role = $_GET['role'] ?? null;
            $users = $this->adminRepository->getAllUsersByRole($role);

            Logger::info("Admin retrieved all users" . ($role ? " with role $role" : ""));
            ApiHelper::sendResponse(200, $users, 'Users retrieved successfully.');
        } catch (Exception $e) {
            Logger::error("Failed to retrieve users. Error: " . $e->getMessage());
            ApiHelper::sendError(500, 'Failed to retrieve users.');
        }
    }

    /**
     * Delete a user by ID
     * Method: DELETE /admin/user
     * @throws JsonException
     */
    public function deleteUser()
    {
        AuthHelper::requireRole('admin');
        $input = ApiHelper::getJsonInput();
        ApiHelper::validateRequest(['user_id'], $input);

        try {
            $result = $this->adminRepository->deleteUserById($input['user_id']);
            if ($result) {
                Logger::info("Admin deleted user with ID: " . $input['user_id']);
                ApiHelper::sendResponse(200, [], 'User deleted successfully.');
            } else {
                throw new RuntimeException("Database operation failed.");
            }
        } catch (Exception $e) {
            Logger::error("Failed to delete user ID: " . $input['user_id'] . " Error: " . $e->getMessage());
            ApiHelper::sendError(500, 'Failed to delete user.');
        }
    }

    /**
     * Get all reported messages
     * Method: GET /admin/reported-messages
     */
    public function getReportedMessages()
    {
        AuthHelper::requireRole('admin');
        try {
            $reports = $this->adminRepository->getReportedMessages();
            Logger::info("Admin retrieved all reported messages.");
            ApiHelper::sendResponse(200, $reports, 'Reported messages retrieved successfully.');
        } catch (Exception $e) {
            Logger::error("Failed to retrieve reported messages. Error: " . $e->getMessage());
            ApiHelper::sendError(500, 'Failed to retrieve reported messages.');
        }
    }

    /**
     * Delete a message by ID
     * Method: DELETE /admin/message
     * @throws JsonException
     */
    public function deleteMessage()
    {
        AuthHelper::requireRole('admin');
        $input = ApiHelper::getJsonInput();
        ApiHelper::validateRequest(['message_id'], $input);

        try {
            $result = $this->adminRepository->deleteMessage($input['message_id']);
            if ($result) {
                Logger::info("Admin deleted message with ID: " . $input['message_id']);
                ApiHelper::sendResponse(200, [], 'Message deleted successfully.');
            } else {
                throw new RuntimeException("Database operation failed.");
            }
        } catch (Exception $e) {
            Logger::error("Failed to delete message ID: " . $input['message_id'] . " Error: " . $e->getMessage());
            ApiHelper::sendError(500, 'Failed to delete message.');
        }
    }

    /**
     * Update a message content (e.g., censor inappropriate content)
     * Method: PUT /admin/message
     * @throws JsonException
     */
    public function updateMessage()
    {
        AuthHelper::requireRole('admin');
        $input = ApiHelper::getJsonInput();
        ApiHelper::validateRequest(['message_id', 'content'], $input);

        try {
            $result = $this->adminRepository->updateMessage($input['message_id'], $input['content']);
            if ($result) {
                Logger::info("Admin updated message with ID: " . $input['message_id']);
                ApiHelper::sendResponse(200, [], 'Message updated successfully.');
            } else {
                throw new Exception("Database operation failed.");
            }
        } catch (Exception $e) {
            Logger::error("Failed to update message ID: " . $input['message_id'] . " Error: " . $e->getMessage());
            ApiHelper::sendError(500, 'Failed to update message.');
        }
    }

    /**
     * Get details of a specific user by ID
     * Method: GET /admin/user
     * @throws JsonException
     */
    public function getUserDetails()
    {
        AuthHelper::requireRole('admin');
        $userId = $_GET['user_id'] ?? null;

        if (!$userId) {
            Logger::error("User details retrieval failed: Missing user_id parameter.");
            ApiHelper::sendError(400, 'Missing user_id parameter.');
        }

        try {
            $user = $this->userRepository->getUserById($userId);
            if ($user) {
                Logger::info("Admin retrieved details for user ID: $userId");
                ApiHelper::sendResponse(200, (array)$user, 'User details retrieved successfully.');
            } else {
                ApiHelper::sendError(404, 'User not found.');
            }
        } catch (Exception $e) {
            Logger::error("Failed to retrieve details for user ID: $userId. Error: " . $e->getMessage());
            ApiHelper::sendError(500, 'Failed to retrieve user details.');
        }
    }
}
