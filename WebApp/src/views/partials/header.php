<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Feedback System'; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Global CSS -->
    <link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon">
</head>
<body>
<header>
    <div class="container">
        <h1 class="logo"><a href="/">Feedback System</a></h1>
        <nav>
            <ul class="nav-links">
                <?php if (isset($_SESSION['user'])): ?>
                    <li><a href="/dashboard.php">Dashboard</a></li>
                    <?php if ($_SESSION['user']['role'] === 'student'): ?>
                        <li><a href="/student/messages.php">My Messages</a></li>
                    <?php elseif ($_SESSION['user']['role'] === 'lecturer'): ?>
                        <li><a href="/lecturer/courses.php">My Courses</a></li>
                    <?php elseif ($_SESSION['user']['role'] === 'admin'): ?>
                        <li><a href="/admin/users.php">Manage Users</a></li>
                    <?php endif; ?>
                    <li><a href="/auth/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="/auth/login.php">Login</a></li>
                    <li><a href="/auth/register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
