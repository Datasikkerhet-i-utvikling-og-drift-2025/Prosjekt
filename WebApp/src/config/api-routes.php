<?php

require_once __DIR__ . '/app.php';
require_once __DIR__ . '/../controllers/StudentController.php';
require_once __DIR__ . '/../controllers/GuestController.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/LecturerController.php';
require_once __DIR__ . '/../controllers/AdminController.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../helpers/Logger.php';
require_once __DIR__ . '/versionURL.php';

use db\Database;

Logger::info('Initializing application...');

// Initialize database connection
try {
    $db = new Database();
    $pdo = $db->getConnection();
    Logger::info('Database connection initialized successfully.');
} catch (Exception $e) {
    Logger::error('Error initializing database: ' . $e->getMessage());
    http_response_code(500);
    die('Internal server error. Please check logs.');
}

// Create controller instances
try {
    $authController = new AuthController($pdo);
    $studentController = new StudentController($pdo);
    $lecturerController = new LecturerController($pdo);
    $adminController = new AdminController($pdo);
    $guestController = new GuestController($pdo);

    Logger::info('Controllers initialized successfully.');
} catch (Exception $e) {
    Logger::error('Error initializing controllers: ' . $e->getMessage());
    http_response_code(500);
    die('Internal server error while initializing controllers.');
}

// Initialize routes array
$routes = [];

try {
    // Define routes
    $routes = [
        // Auth routes
        ['POST', API_BASE_URL.'/auth/register', [$authController, 'register']],
        ['POST', API_BASE_URL.'/auth/login', [$authController, 'login']],
        ['GET', API_BASE_URL.'/auth/logout', [$authController, 'logout']],
        ['POST', API_BASE_URL.'/auth/change-password', [$authController, 'changePassword']],
        ['POST', API_BASE_URL.'/auth/password-reset/request', [$authController, 'requestPasswordReset']],
        ['POST', API_BASE_URL.'/auth/password-reset', [$authController, 'resetPassword']],
        ['POST', API_BASE_URL.'/auth/change-password', [$authController, 'changePassword']],
    
    

        // Student routes
        ['GET', API_BASE_URL.'/student/courses', [$studentController, 'getCourses']],
        ['GET', API_BASE_URL.'/student/messages', [$studentController, 'getMyMessages']],
        ['POST', API_BASE_URL.'/student/message/send', [$studentController, 'sendMessage']],

        // Lecturer routes
        ['GET', API_BASE_URL.'/lecturer/courses', [$lecturerController, 'getCourses']],
        ['POST', API_BASE_URL.'/lecturer/courses', [$lecturerController, 'getCourses']],
        ['POST', API_BASE_URL.'/lecturer/messages', [$lecturerController, 'getMessagesForCourse']],
        ['POST', API_BASE_URL.'/lecturer/message/reply', [$lecturerController, 'replyToMessage']],
        ['POST', API_BASE_URL.'/lecturer/message/resolve', [$lecturerController, 'markMessageAsResolved']],

        // Admin routes
        ['GET', API_BASE_URL.'/admin/users', [$adminController, 'getAllUsers']],
        ['POST', API_BASE_URL.'/admin/user/delete', [$adminController, 'deleteUser']],
        ['GET', API_BASE_URL.'/admin/messages/reported', [$adminController, 'getReportedMessages']],
        ['POST', API_BASE_URL.'/admin/message/delete', [$adminController, 'deleteMessage']],
        ['POST', API_BASE_URL.'/admin/message/update', [$adminController, 'updateMessage']],
        ['GET', API_BASE_URL.'/admin/user/details', [$adminController, 'getUserDetails']],

        // Guest routes
        ['GET', API_BASE_URL.'/guest/messages', [$guestController, 'getMessages']],
        ['POST', API_BASE_URL.'/guest/messages/view', [$guestController, 'viewMessages']],
        ['POST', API_BASE_URL.'/guest/messages/report', [$guestController, 'reportMessage']],
        ['POST', API_BASE_URL.'/guest/messages/comment', [$guestController, 'addComment']],
    ];

    Logger::info('Routes initialized successfully.');
} catch (Exception $e) {
    Logger::error('Error initializing routes: ' . $e->getMessage());
    http_response_code(500);
    die('Internal server error while initializing routes.');
}

// Optional: Add a debug route for development
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

// Log all routes to a file
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
