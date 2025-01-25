<nav class="navbar">
    <div class="container">
        <a href="/" class="navbar-logo">Feedback System</a>
        <ul class="navbar-links">
            <?php if (isset($_SESSION['user'])): ?>
                <!-- Links for logged-in users -->
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
                <!-- Links for guests -->
                <li><a href="/auth/login.php">Login</a></li>
                <li><a href="/auth/register.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
