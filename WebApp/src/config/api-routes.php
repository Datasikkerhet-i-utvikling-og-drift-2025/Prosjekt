<?php

require_once __DIR__ . '/app.php';
require_once __DIR__ . '/../controllers/StudentController.php';
require_once __DIR__ . '/../controllers/GuestController.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/LecturerController.php';
require_once __DIR__ . '/../controllers/AdminController.php';
require_once __DIR__ . '/Database.php';

use db\Database;

// Initialize database connection
try {
    $db = new Database();
    $pdo = $db->getConnection();
} catch (Exception $e) {
    error_log('Error initializing database: ' . $e->getMessage(), 3, __DIR__ . '/../logs/error.log');
    die('Internal server error');
}

// Create controller instances
$authController = new AuthController($pdo);
$studentController = new StudentController($pdo);
$lecturerController = new LecturerController($pdo);
$adminController = new AdminController($pdo);
$guestController = new GuestController($pdo);

// Initialize routes
$routes = [];

// Add routes
$routes = array_merge($routes, [
    // Auth routes
    ['POST', '/auth/register', [$authController, 'register']],
    ['POST', '/auth/login', [$authController, 'login']],
    ['POST', '/auth/logout', [$authController, 'logout']],
    ['POST', '/auth/password-reset/request', [$authController, 'requestPasswordReset']],
    ['POST', '/auth/password-reset', [$authController, 'resetPassword']],

    // Student routes
    ['GET', '/student/courses', [$studentController, 'getCourses']],
    ['GET', '/student/messages', [$studentController, 'getMyMessages']],
    ['POST', '/student/message/send', [$studentController, 'sendMessage']],

    // Lecturer routes
    ['GET', '/lecturer/courses', [$lecturerController, 'getCourses']],
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
    ['POST', '/guest/messages/view', [$guestController, 'viewMessages']],
    ['POST', '/guest/messages/report', [$guestController, 'reportMessage']],
    ['POST', '/guest/messages/comment', [$guestController, 'addComment']],

]);
/*
// Add debug route (added after $routes is defined)
$routes[] = ['GET', '/debug/routes', function () use ($routes) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_map(function ($route) {
        return [
            'method' => $route[0],
            'uri' => $route[1],
            'controller' => is_array($route[2]) ? get_class($route[2][0]) : 'Closure',
            'action' => is_array($route[2]) ? $route[2][1] : null
        ];
    }, $routes), JSON_PRETTY_PRINT);
    exit;
}];
*/
// Return routes to the main entry point
return $routes;


// Log all routes to a file
$logFile = __DIR__ . '/../logs/routes.log';
file_put_contents($logFile, json_encode($routes, JSON_PRETTY_PRINT));
