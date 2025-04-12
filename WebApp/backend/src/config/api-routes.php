<?php

namespace config;

require_once __DIR__ . '/app.php';
require_once __DIR__ . '/../controllers/v1/V1StudentController.php';
require_once __DIR__ . '/../controllers/v1/V1GuestController.php';
require_once __DIR__ . '/../controllers/v1/V1AuthController.php';
require_once __DIR__ . '/../controllers/v1/V1LecturerController.php';
require_once __DIR__ . '/../controllers/v1/V1AdminController.php';
require_once __DIR__ . '/../managers/DatabaseManager.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../helpers/Logger.php';

use controllers\v1\V1AdminController;
use controllers\v1\V1AuthController;
use controllers\v1\V1GuestController;
use controllers\v1\V1LecturerController;
use controllers\v1\V1StudentController;
use Exception;
use helpers\AccessControlManager;
use helpers\GrayLogger;
use managers\DatabaseManager;
use managers\JWTManager;
use models\Student;
use repositories\GuestRepository;
use repositories\LecturerRepository;
use repositories\StudentRepository;
use repositories\UserRepository;
use services\AuthService;
use services\GuestService;
use services\LecturerService;
use services\StudentService;

$logger = GrayLogger::getInstance();


// Log application startup
//$logger->info('Initializing api...');

try {
    // Initialize manager classes
    $userDb = new DatabaseManager("user");
    $pdo = $userDb->connectToDb();

    $lecturerDb = new DatabaseManager("lecturer");
    $pdo = $lecturerDb->connectToDb();

    $studentDb = new DatabaseManager("student");
    $pdo = $studentDb->connectToDb();

    $guestDb = new DatabaseManager("guest");
    $pdo = $guestDb->connectToDb();

    //$accessControlManager = new AccessControlManager();
    $jwtManager = new JWTManager();
    //$sessionManager = new SessionManager();


    // Initialize repository classes
    $userRepository = new UserRepository($userDb);
    $lecturerRepository = new LecturerRepository($lecturerDb);
    $guestRepository = new GuestRepository($guestDb);
    $studentRepository = new StudentRepository($studentDb);


    // Initialize service classes
    $authService = new AuthService($userRepository, $lecturerRepository, $jwtManager);
    $lecturerService = new LecturerService($lecturerRepository);
    $guestService = new GuestService($guestRepository);
    $studentService = new StudentService($studentRepository);

    // Create controller instances
    $authController = new V1AuthController($authService);
    $studentController = new V1StudentController($studentService);
    $lecturerController = new V1LecturerController($lecturerService);
    $guestController = new V1GuestController($guestService);

    //$logger->info('Controllers initialized successfully.');
} catch (Exception $e) {
    $logger->error('Error initializing components: ' . $e->getMessage());
    http_response_code(500);
    die('Internal server error. Check logs for details.');
}

// Initialize API routes
$routes = [];

try {
    $routes = [
        // Auth routes
        ['POST', '/api/v1/auth/register', [$authController, 'register']],
        ['POST', '/api/v1/auth/login', [$authController, 'login']],
        ['GET', '/api/v1/auth/logout', [$authController, 'logout']],

        //['POST', '/api/auth/register', [$authController, 'register']],
        //['POST', '/api/auth/login', [$authController, 'login']],
        //['GET', '/api/auth/logout', [$authController, 'logout']],
        //['POST', '/api/auth/change-password', [$authController, 'changePassword']],
        ['POST', '/api/v1/auth/password-reset/request', [$authController, 'requestPasswordReset']],
        ['POST', '/api/v1/auth/password-reset', [$authController, 'resetPassword']],

        // Student routes
        //['GET', '/api/student/courses', [$studentController, 'getCourses']],
        //['GET', '/api/student/messages', [$studentController, 'getMyMessages']],
        //['POST', '/api/v1/student/message/send', [$studentController, 'sendMessage']],

        // Lecturer routes
        //['GET', '/api/lecturer/courses', [$lecturerController, 'getCourses']],
        //['GET', '/api/v1/lecturer/messages', [$lecturerController, 'getMessages']], //'getMessagesForCourse'
        //['POST', '/api/v1/lecturer/message/reply', [$lecturerController, 'sendReply']], //'replyToMessage'
        //['POST', '/api/lecturer/message/resolve', [$lecturerController, 'markMessageAsResolved']],

        // Admin routes
        //['GET', '/api/admin/users', [$adminController, 'getAllUsers']],
        //['POST', '/api/admin/user/delete', [$adminController, 'deleteUser']],
        //['GET', '/api/admin/messages/reported', [$adminController, 'getReportedMessages']],
        //['POST', '/api/admin/message/delete', [$adminController, 'deleteMessage']],
        //['POST', '/api/admin/message/update', [$adminController, 'updateMessage']],
        //['GET', '/api/admin/user/details', [$adminController, 'getUserDetails']],

        // Guest routes
        ['GET', '/api/v1/guest/authorize', [$guestController, 'authorizePin']],
        ['GET', '/api/v1/guest/messages', [$guestController, 'getMessagesByCourse']],
        ['POST', '/api/v1/guest/messages/report', [$guestController, 'reportMessage']],
        ['POST', '/api/v1/guest/messages/comment', [$guestController, 'sendComment']],
    ];

    //$logger->info('Routes initialized successfully.');
} catch (Exception $e) {
    $this->logger->error('Error initializing routes: ' . $e->getMessage());
    http_response_code(500);
    die('Internal server error while initializing routes.');
}

// Return routes to the main entry point
//$logger->info('Api initialized successfully.');
return $routes;




