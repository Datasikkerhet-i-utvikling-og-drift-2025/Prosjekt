<?php
require_once __DIR__ . '/versionURL.php';
return [
    APP_BASE_URL.'/' => __DIR__ . '/../views/auth/login.php',
    APP_BASE_URL.'/register' => __DIR__ . '/../views/auth/register.php',
    APP_BASE_URL.'/reset-password' => __DIR__ . '/../views/auth/reset-password.php',
    APP_BASE_URL.'/change-password' => __DIR__ . '/../views/auth/change-password.php',
    APP_BASE_URL.'/student/dashboard' => __DIR__ . '/../views/student/dashboard.php',
    APP_BASE_URL.'/student/send-message' => __DIR__ . '/../views/student/send-message.php',
    APP_BASE_URL.'/student/view-responses' => __DIR__ . '/../views/student/view-responses.php',
    APP_BASE_URL.'/lecturer/dashboard' => __DIR__ . '/../views/lecturer/dashboard.php',
    APP_BASE_URL.'/lecturer/read-messages' => __DIR__ . '/../views/lecturer/read-messages.php',
    APP_BASE_URL.'/lecturer/reply' => __DIR__ . '/../views/lecturer/reply.php',
    APP_BASE_URL.'/lecturer/courses' => __DIR__ . '/../views/lecturer/courses.php',
    APP_BASE_URL.'/admin/dashboard' => __DIR__ . '/../views/admin/dashboard.php',
    APP_BASE_URL.'/admin/manage-users' => __DIR__ . '/../views/admin/manage-users.php',
    APP_BASE_URL.'/admin/manage-messages' => __DIR__ . '/../views/admin/manage-messages.php',
    APP_BASE_URL.'/admin/reports' => __DIR__ . '/../views/admin/reports.php',
    APP_BASE_URL.'/guests/view-messages' => __DIR__ . '/../views/guests/view-messages.php',
    APP_BASE_URL.'/guests/report-message' => __DIR__ . '/../views/guests/report-message.php',
    APP_BASE_URL.'/guests/dashboard' => __DIR__ . '/../views/guests/dashboard.php',
    APP_BASE_URL.'/auth/guest' => __DIR__ . '/../controllers/AuthController.php@guest', // Added route for guest handler
    APP_BASE_URL.'/profile' => __DIR__ . '/../views/profile/index.php',
    '/dokumentasjon' => __DIR__ . '/../views/documentation/dokumentasjon.html',
    API_BASE_URL => __DIR__ . '/../views/documentation/api.html',

];
