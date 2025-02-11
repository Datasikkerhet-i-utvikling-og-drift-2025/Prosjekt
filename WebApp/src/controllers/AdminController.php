<?php

namespace controllers;

require_once __DIR__ . '/../helpers/ApiHelper.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';
require_once __DIR__ . '/../helpers/Logger.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Message.php';

class AdminController
{
    private $userModel;
    private $messageModel;

    public function __construct($pdo)
    {
        $this->userModel = new User($pdo);
        $this->messageModel = new Message($pdo);
    }

    // Get all users (optional filtering by role)
    public function getAllUsers()
    {
        AuthHelper::requireRole('admin');

        try {
            $role = $_GET['role'] ?? null;
            $users = $this->userModel->getAllUsers($role);

            Logger::info("Admin retrieved all users" . ($role ? " with role $role" : ""));
            ApiHelper::sendResponse(200, $users, 'Users retrieved successfully.');
        } catch (Exception $e) {
            Logger::error("Failed to retrieve users. Error: " . $e->getMessage());
            ApiHelper::sendError(500, 'Failed to retrieve users.');
        }
    }

    // Delete a user by ID
    public function deleteUser()
    {
        AuthHelper::requireRole('admin');

        $input = ApiHelper::getJsonInput();
        ApiHelper::validateRequest(['user_id'], $input);

        try {
            $result = $this->userModel->deleteUser($input['user_id']);

            if ($result) {
                Logger::info("Admin deleted user with ID: " . $input['user_id']);
                ApiHelper::sendResponse(200, [], 'User deleted successfully.');
            } else {
                throw new Exception("Database operation failed.");
            }
        } catch (Exception $e) {
            Logger::error("Failed to delete user ID: " . $input['user_id'] . ". Error: " . $e->getMessage());
            ApiHelper::sendError(500, 'Failed to delete user.');
        }
    }

    // View all reported messages
    public function getReportedMessages()
    {
        AuthHelper::requireRole('admin');

        try {
            $reports = $this->messageModel->getReportedMessages();

            Logger::info("Admin retrieved all reported messages.");
            ApiHelper::sendResponse(200, $reports, 'Reported messages retrieved successfully.');
        } catch (Exception $e) {
            Logger::error("Failed to retrieve reported messages. Error: " . $e->getMessage());
            ApiHelper::sendError(500, 'Failed to retrieve reported messages.');
        }
    }

    // Delete a message by ID
    public function deleteMessage()
    {
        AuthHelper::requireRole('admin');

        $input = ApiHelper::getJsonInput();
        ApiHelper::validateRequest(['message_id'], $input);

        try {
            $result = $this->messageModel->deleteMessage($input['message_id']);

            if ($result) {
                Logger::info("Admin deleted message with ID: " . $input['message_id']);
                ApiHelper::sendResponse(200, [], 'Message deleted successfully.');
            } else {
                throw new Exception("Database operation failed.");
            }
        } catch (Exception $e) {
            Logger::error("Failed to delete message ID: " . $input['message_id'] . ". Error: " . $e->getMessage());
            ApiHelper::sendError(500, 'Failed to delete message.');
        }
    }

    // Update a message (e.g., censor content)
    public function updateMessage()
    {
        AuthHelper::requireRole('admin');

        $input = ApiHelper::getJsonInput();
        ApiHelper::validateRequest(['message_id', 'content'], $input);

        try {
            $result = $this->messageModel->updateMessage($input['message_id'], $input['content']);

            if ($result) {
                Logger::info("Admin updated message with ID: " . $input['message_id']);
                ApiHelper::sendResponse(200, [], 'Message updated successfully.');
            } else {
                throw new Exception("Database operation failed.");
            }
        } catch (Exception $e) {
            Logger::error("Failed to update message ID: " . $input['message_id'] . ". Error: " . $e->getMessage());
            ApiHelper::sendError(500, 'Failed to update message.');
        }
    }

    // View details of a specific user by ID
    public function getUserDetails()
    {
        AuthHelper::requireRole('admin');

        $userId = $_GET['user_id'] ?? null;

        if (!$userId) {
            Logger::error("User details retrieval failed: Missing user_id parameter.");
            ApiHelper::sendError(400, 'Missing user_id parameter.');
        }

        try {
            $user = $this->userModel->getUserById($userId);

            if ($user) {
                Logger::info("Admin retrieved details for user ID: $userId");
                ApiHelper::sendResponse(200, $user, 'User details retrieved successfully.');
            } else {
                ApiHelper::sendError(404, 'User not found.');
            }
        } catch (Exception $e) {
            Logger::error("Failed to retrieve details for user ID: $userId. Error: " . $e->getMessage());
            ApiHelper::sendError(500, 'Failed to retrieve user details.');
        }
    }
}
