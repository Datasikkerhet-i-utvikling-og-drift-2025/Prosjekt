<nav class="navbar">
    <div class="navbar-container">
        <a href="/" class="navbar-logo">Feedback System</a>
        <ul class="navbar-links">
            <?php if (isset($_SESSION['user'])): ?>
                <!-- Links for logged-in users -->
                <li><a href="/dashboard">Dashboard</a></li>
                <?php if ($_SESSION['user']['role'] === 'student'): ?>
                    <li><a href="/student/view-responses">My Messages</a></li>
                <?php elseif ($_SESSION['user']['role'] === 'lecturer'): ?>
                    <li><a href="/lecturer/dashboard">My Courses</a></li>
                <?php elseif ($_SESSION['user']['role'] === 'admin'): ?>
                    <li><a href="/admin/dashboard">Manage Users</a></li>
                <?php endif; ?>
                <li><a href="/auth/logout">Logout</a></li>
            <?php else: ?>
                <!-- Links for guests -->
                <li><a href="/auth/login">Login</a></li>
                <li><a href="/auth/register">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
