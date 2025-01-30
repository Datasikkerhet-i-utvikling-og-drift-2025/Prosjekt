<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Feedback System', ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Global CSS -->
    <link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon">
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
                    <!-- Common Dashboard Link -->
                    <li><a href="<?php echo ($_SESSION['user']['role'] === 'student') ? '/student/dashboard' : (($_SESSION['user']['role'] === 'lecturer') ? '/lecturer/dashboard' : '/admin/dashboard'); ?>">Dashboard</a></li>

                    <?php if ($_SESSION['user']['role'] === 'student'): ?>
                        <li><a href="/student/view-responses">My Messages</a></li>
                        <li><a href="/student/send-message">Send Message</a></li>
                    <?php elseif ($_SESSION['user']['role'] === 'lecturer'): ?>
                        <li><a href="/lecturer/dashboard">My Courses</a></li>
                        <li><a href="/lecturer/read-messages">Messages</a></li>
                    <?php elseif ($_SESSION['user']['role'] === 'admin'): ?>
                        <li><a href="/admin/manage-users">Manage Users</a></li>
                        <li><a href="/admin/manage-messages">Manage Messages</a></li>
                        <li><a href="/admin/reports">View Reports</a></li>
                    <?php endif; ?>
                    <li><a href="/auth/logout" class="logout-link">Logout</a></li>
                <?php else: ?>
                    <li><a href="/">Login</a></li>
                    <li><a href="/register">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
