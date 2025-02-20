<?php

namespace config;

return [
    APP_BASE_URL.'/' => __DIR__ . '/../views/auth/login.php',
    APP_BASE_URL => __DIR__ . '/../views/auth/login.php',
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
    // Authentication Views
    '/'                    => __DIR__ . '/../views/auth/login.php',
    '/register'            => __DIR__ . '/../views/auth/register.php',
    '/reset-password'      => __DIR__ . '/../views/auth/reset-password.php',
    '/change-password'     => __DIR__ . '/../views/auth/change-password.php',

    // Student Views
    '/student/dashboard'      => __DIR__ . '/../views/student/dashboard.php',
    '/student/send-message'   => __DIR__ . '/../views/student/send-message.php',
    '/student/view-responses' => __DIR__ . '/../views/student/view-responses.php',

    // Lecturer Views
    '/lecturer/dashboard'     => __DIR__ . '/../views/lecturer/dashboard.php',
    '/lecturer/read-messages' => __DIR__ . '/../views/lecturer/read-messages.php',
    '/lecturer/reply'         => __DIR__ . '/../views/lecturer/reply.php',
    '/lecturer/courses'       => __DIR__ . '/../views/lecturer/courses.php',

    // Admin Views
    '/admin/dashboard'       => __DIR__ . '/../views/admin/dashboard.php',
    '/admin/manage-users'    => __DIR__ . '/../views/admin/manage-users.php',
    '/admin/manage-messages' => __DIR__ . '/../views/admin/manage-messages.php',
    '/admin/reports'         => __DIR__ . '/../views/admin/reports.php',

    // Guest Views
    '/guest/view-messages'   => __DIR__ . '/../views/guest/view-messages.php',
    '/guest/report-message'  => __DIR__ . '/../views/guest/report-message.php',
    '/guest/dashboard'       => __DIR__ . '/../views/guest/dashboard.php',

    // Profile Page
    '/profile' => __DIR__ . '/../views/profile/index.php',

    // Special Routes
    '/auth/guest' => ['AuthController', 'guest'], // Corrected guest handling route
];
