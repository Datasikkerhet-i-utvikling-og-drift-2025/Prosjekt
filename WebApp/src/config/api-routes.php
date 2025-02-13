<?php

require_once __DIR__ . '/app.php';
require_once __DIR__ . '/../controllers/StudentController.php';
require_once __DIR__ . '/../controllers/GuestController.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/LecturerController.php';
require_once __DIR__ . '/../controllers/AdminController.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../helpers/Logger.php';

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
        ['POST', '/auth/register', [$authController, 'register']],
        ['POST', '/auth/login', [$authController, 'login']],
        ['GET', '/auth/logout', [$authController, 'logout']],
        ['POST', '/auth/password-reset/request', [$authController, 'requestPasswordReset']],
        ['POST', '/auth/password-reset', [$authController, 'resetPassword']],
        ['POST', '/auth/change-password', [$authController, 'changePassword']],
    
    

        // Student routes
        ['GET', '/student/courses', [$studentController, 'getCourses']],
        ['GET', '/student/messages', [$studentController, 'getMyMessages']],
        ['POST', '/student/message/send', [$studentController, 'sendMessage']],

        // Lecturer routes
        ['GET', '/lecturer/courses', [$lecturerController, 'getCourses']],
        ['POST', '/lecturer/courses', [$lecturerController, 'getCourses']],
        ['POST', '/lecturer/messages', [$lecturerController, 'getMessagesForCourse']],
        ['POST', '/lecturer/message/reply', [$lecturerController, 'replyToMessage']],
        ['POST', '/lecturer/message/resolve', [$lecturerController, 'markMessageAsResolved']],

        // Admin routes
        ['GET', '/admin/users', [$adminController, 'getAllUsers']],
        ['POST', '/admin/user/delete', [$adminController, 'deleteUser']],
        ['GET', '/admin/messages/reported', [$adminController, 'getReportedMessages']],
        ['POST', '/admin/message/delete', [$adminController, 'deleteMessage']],
        ['POST', '/admin/message/update', [$adminController, 'updateMessage']],
        ['GET', '/admin/user/details', [$adminController, 'getUserDetails']],

        // Guest routes
        ['GET', '/guest/messages', [$guestController, 'getMessages']],
        ['POST', '/guest/messages/view', [$guestController, 'viewMessages']],
        ['POST', '/guest/messages/report', [$guestController, 'reportMessage']],
        ['POST', '/guest/messages/comment', [$guestController, 'addComment']],
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
