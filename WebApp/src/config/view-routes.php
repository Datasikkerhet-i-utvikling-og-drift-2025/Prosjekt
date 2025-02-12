<?php

return [
    '/' => __DIR__ . '/../views/auth/login.php',
    '/register' => __DIR__ . '/../views/auth/register.php',
    '/reset-password' => __DIR__ . '/../views/auth/reset-password.php',
    '/change-password' => __DIR__ . '/../views/auth/change-password.php',
    '/student/dashboard' => __DIR__ . '/../views/student/dashboard.php',
    '/student/send-message' => __DIR__ . '/../views/student/send-message.php',
    '/student/view-responses' => __DIR__ . '/../views/student/view-responses.php',
    '/lecturer/dashboard' => __DIR__ . '/../views/lecturer/dashboard.php',
    '/lecturer/read-messages' => __DIR__ . '/../views/lecturer/read-messages.php',
    '/lecturer/reply' => __DIR__ . '/../views/lecturer/reply.php',
    '/admin/dashboard' => __DIR__ . '/../views/admin/dashboard.php',
    '/admin/manage-users' => __DIR__ . '/../views/admin/manage-users.php',
    '/admin/manage-messages' => __DIR__ . '/../views/admin/manage-messages.php',
    '/admin/reports' => __DIR__ . '/../views/admin/reports.php',
    '/guests/view-messages' => __DIR__ . '/../views/guests/view-messages.php',
    '/guests/report-message' => __DIR__ . '/../views/guests/report-message.php',
    '/guests/dashboard' => __DIR__ . '/../views/guests/dashboard.php',
    '/auth/guest' => __DIR__ . '/../controllers/AuthController.php@guest',
    '/profile' => __DIR__ . '/../views/profile/index.php', 
];
