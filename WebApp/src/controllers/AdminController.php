<?php

require_once '../src/helpers/ApiHelper.php';
require_once '../src/helpers/AuthHelper.php';
require_once '../src/models/User.php';
require_once '../src/models/Message.php';

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

        $role = $_GET['role'] ?? null;
        $users = $this->userModel->getAllUsers($role);

        ApiHelper::sendResponse(200, $users, 'Users retrieved successfully.');
    }

    // Delete a user by ID
    public function deleteUser()
    {
        AuthHelper::requireRole('admin');

        $input = ApiHelper::getJsonInput();
        ApiHelper::validateRequest(['user_id'], $input);

        $result = $this->userModel->deleteUser($input['user_id']);

        if ($result) {
            ApiHelper::sendResponse(200, [], 'User deleted successfully.');
        } else {
            ApiHelper::sendError(500, 'Failed to delete user.');
        }
    }

    // View all reported messages
    public function getReportedMessages()
    {
        AuthHelper::requireRole('admin');

        $reports = $this->messageModel->getReportedMessages();

        ApiHelper::sendResponse(200, $reports, 'Reported messages retrieved successfully.');
    }

    // Delete a message by ID
    public function deleteMessage()
    {
        AuthHelper::requireRole('admin');

        $input = ApiHelper::getJsonInput();
        ApiHelper::validateRequest(['message_id'], $input);

        $result = $this->messageModel->deleteMessage($input['message_id']);

        if ($result) {
            ApiHelper::sendResponse(200, [], 'Message deleted successfully.');
        } else {
            ApiHelper::sendError(500, 'Failed to delete message.');
        }
    }

    // Update a message (e.g., censor content)
    public function updateMessage()
    {
        AuthHelper::requireRole('admin');

        $input = ApiHelper::getJsonInput();
        ApiHelper::validateRequest(['message_id', 'content'], $input);

        $result = $this->messageModel->updateMessage($input['message_id'], $input['content']);

        if ($result) {
            ApiHelper::sendResponse(200, [], 'Message updated successfully.');
        } else {
            ApiHelper::sendError(500, 'Failed to update message.');
        }
    }

    // View details of a specific user by ID
    public function getUserDetails()
    {
        AuthHelper::requireRole('admin');

        $userId = $_GET['user_id'] ?? null;

        if (!$userId) {
            ApiHelper::sendError(400, 'Missing user_id parameter.');
        }

        $user = $this->userModel->getUserById($userId);

        if ($user) {
            ApiHelper::sendResponse(200, $user, 'User details retrieved successfully.');
        } else {
            ApiHelper::sendError(404, 'User not found.');
        }
    }
}
