<?php
require_once __DIR__ . '/../../config/versionURL.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Feedback System', ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Global CSS -->
    <link rel="icon" href="https://img.icons8.com/color/96/anonymous-mask.png" type="image/x-icon">
</head>
<body>
<header class="header">
    <div class="header-container">
        <!-- Logo Section -->
        <div class="logo">
            <a href="<?= APP_BASE_URL ?>/" title="Feedback System Home">Feedback System</a>
        </div>


        <!-- Navigation Menu -->
        <nav>
            <ul class="nav-links">
                <?php if (isset($_SESSION['user'])): ?>
                    <!-- Common Dashboard Link -->

                    <?php if ($_SESSION['user']['role'] === 'student'): ?>
                        <li><a href="<?= APP_BASE_URL ?>/student/dashboard">Dashboard</a></li>
                        <li><a href="<?= APP_BASE_URL ?>/student/view-responses">My Messages</a></li>
                        <li><a href="<?= APP_BASE_URL ?>/student/send-message">Send Message</a></li>
                        <li><a href="<?= APP_BASE_URL ?>/profile">My Profile</a></li>
                    <?php elseif ($_SESSION['user']['role'] === 'lecturer'): ?>
                        <li><a href="<?= APP_BASE_URL ?>/lecturer/dashboard">Dashboard</a></li>
                       
                        <li><a href="<?= APP_BASE_URL ?>/lecturer/courses">Courses</a></li>
                        <li><a href="<?= APP_BASE_URL ?>/lecturer/read-messages">Messages</a></li>
                        <li><a href="<?= APP_BASE_URL ?>/profile">My Profile</a></li>
                    <?php elseif ($_SESSION['user']['role'] === 'admin'): ?>
                        <li><a href="<?= APP_BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                        <li><a href="<?= APP_BASE_URL ?>/admin/manage-users">Manage Users</a></li>
                        <li><a href="<?= APP_BASE_URL ?>/admin/manage-messages">Manage Messages</a></li>
                        <li><a href="<?= APP_BASE_URL ?>/admin/reports">View Reports</a></li>
                        <li><a href="<?= APP_BASE_URL ?>/profile">My Profile</a></li>
                    <?php endif; ?>
                        <li><a href="<?= API_BASE_URL ?>/auth/logout" class="logout-link">Logout</a></li>
                    <?php else: ?>
                        <li><a href="<?= APP_BASE_URL ?>/">Login</a></li>
                        <li><a href="<?= APP_BASE_URL ?>/register">Register</a></li>
                    <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
