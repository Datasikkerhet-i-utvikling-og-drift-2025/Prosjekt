<?php
require_once __DIR__ . '/../../vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Feedback System', ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" href="/assets/images/icon.png" type="image/x-icon">
</head>
<body>
<header class="header">
    <div class="header-container">
        <!-- Logo Section -->
        <div class="logo">
            <a href="/" title="Feedback System Home">Feedback System</a>
        </div>

        <!-- Navigation Menu -->
        <nav>
            <ul class="nav-links">
                <?php if (isset($_SESSION['user'])): ?>
                    <?php $role = $_SESSION['user']['role'] ?? ''; ?>

                    <?php if ($role === 'student'): ?>
                        <li><a href="/student/dashboard">Dashboard</a></li>
                        <li><a href="/student/view-responses">My Messages</a></li>
                        <li><a href="/student/send-message">Send Message</a></li>
                        <li><a href="/profile">My Profile</a></li>

                    <?php elseif ($role === 'lecturer'): ?>
                        <li><a href="/lecturer/dashboard">Dashboard</a></li>
                        <li><a href="/lecturer/courses">Courses</a></li>
                        <li><a href="/lecturer/read-messages">Messages</a></li>
                        <li><a href="/profile">My Profile</a></li>

                    <?php elseif ($role === 'admin'): ?>
                        <li><a href="/admin/dashboard">Dashboard</a></li>
                        <li><a href="/admin/manage-users">Manage Users</a></li>
                        <li><a href="/admin/manage-messages">Manage Messages</a></li>
                        <li><a href="/admin/reports">View Reports</a></li>
                        <li><a href="/profile">My Profile</a></li>
                    <?php endif; ?>

                    <li><a href="/api/v1/auth/logout" class="logout-link">Logout</a></li>
                <?php else: ?>
                    <li><a href="/">Login</a></li>
                    <li><a href="/register">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
