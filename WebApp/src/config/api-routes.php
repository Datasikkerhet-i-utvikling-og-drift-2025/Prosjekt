<?php

namespace config;

require_once __DIR__ . '/app.php';
require_once __DIR__ . '/../controllers/StudentController.php';
require_once __DIR__ . '/../controllers/GuestController.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/LecturerController.php';
require_once __DIR__ . '/../controllers/AdminController.php';
require_once __DIR__ . '/../services/DatabaseManager.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../helpers/Logger.php';

use controllers\AdminController;
use controllers\AuthController;
use controllers\GuestController;
use controllers\LecturerController;
use controllers\StudentController;
use Exception;
use helpers\Logger;
use repositories\UserRepository;
use service\DatabaseService;

// Log application startup
Logger::info('Initializing application...');

try {
    // Initialize database service and repository
    $db = new DatabaseService();
    $pdo = $db->connectToDb();

    Logger::info('DatabaseManager connection initialized successfully.');

    // Create controller instances
    $authController = new AuthController($db);
    //$studentController = new StudentController($pdo);
    //$lecturerController = new LecturerController($pdo);
    $adminController = new AdminController($db);
    //$guestController = new GuestController($pdo);

    Logger::info('Controllers initialized successfully.');
} catch (Exception $e) {
    Logger::error('Error initializing components: ' . $e->getMessage());
    http_response_code(500);
    die('Internal server error. Check logs for details.');
}

// Initialize API routes
$routes = [];

try {
    $routes = [
        // Auth routes
        ['POST', '/api/auth/register', [$authController, 'register']],
        ['POST', '/api/auth/login', [$authController, 'login']],
        ['GET', '/api/auth/logout', [$authController, 'logout']],
        ['POST', '/api/auth/change-password', [$authController, 'changePassword']],
        ['POST', '/api/auth/password-reset/request', [$authController, 'requestPasswordReset']],
        ['POST', '/api/auth/password-reset', [$authController, 'resetPassword']],

        // Student routes
        ['GET', '/api/student/courses', [$studentController, 'getCourses']],
        ['GET', '/api/student/messages', [$studentController, 'getMyMessages']],
        ['POST', '/api/student/message/send', [$studentController, 'sendMessage']],

        // Lecturer routes
        ['GET', '/api/lecturer/courses', [$lecturerController, 'getCourses']],
        ['GET', '/api/lecturer/messages', [$lecturerController, 'getMessagesForCourse']],
        ['POST', '/api/lecturer/message/reply', [$lecturerController, 'replyToMessage']],
        ['POST', '/api/lecturer/message/resolve', [$lecturerController, 'markMessageAsResolved']],

        // Admin routes
        ['GET', '/api/admin/users', [$adminController, 'getAllUsers']],
        ['POST', '/api/admin/user/delete', [$adminController, 'deleteUser']],
        ['GET', '/api/admin/messages/reported', [$adminController, 'getReportedMessages']],
        ['POST', '/api/admin/message/delete', [$adminController, 'deleteMessage']],
        ['POST', '/api/admin/message/update', [$adminController, 'updateMessage']],
        ['GET', '/api/admin/user/details', [$adminController, 'getUserDetails']],

        // Guest routes
        ['GET', '/api/guest/messages', [$guestController, 'getMessages']],
        ['POST', '/api/guest/messages/report', [$guestController, 'reportMessage']],
        ['POST', '/api/guest/messages/comment', [$guestController, 'addComment']],
    ];

    Logger::info('Routes initialized successfully.');
} catch (Exception $e) {
    Logger::error('Error initializing routes: ' . $e->getMessage());
    http_response_code(500);
    die('Internal server error while initializing routes.');
}

// Debug route (only for development)
$routes[] = ['GET', '/debug/routes', function () use ($routes) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_map(static function ($route) {
        return [
            'method' => $route[0],
            'uri' => $route[1],
            'controller' => is_array($route[2]) ? get_class($route[2][0]) : 'Closure',
            'action' => is_array($route[2]) ? $route[2][1] : null,
        ];
    }, $routes), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    Logger::info('Debug route accessed.');
    exit;
}];

// Log routes to a file
$logFile = __DIR__ . '/../../logs/routes.log';
try {
    file_put_contents($logFile, json_encode($routes, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
    Logger::info('Routes logged successfully to ' . $logFile);
} catch (Exception $e) {
    Logger::error('Failed to write routes log: ' . $e->getMessage());
}

// Return routes to the main entry point
Logger::info('Application initialized successfully.');
return $routes;
