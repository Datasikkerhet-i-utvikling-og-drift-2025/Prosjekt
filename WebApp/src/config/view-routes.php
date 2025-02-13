<?php

namespace config;

return [
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
