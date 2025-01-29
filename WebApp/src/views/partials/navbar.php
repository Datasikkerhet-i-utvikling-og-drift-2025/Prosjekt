<nav class="navbar">
    <div class="navbar-container">
        <!-- Logo Section -->
        <a href="/" class="navbar-logo" title="Feedback System Home">Feedback System</a>

        <!-- Navigation Links -->
        <ul class="navbar-links">
            <?php if (isset($_SESSION['user'])): ?>
                <!-- Links for logged-in users -->
                <li><a href="<?php echo ($_SESSION['user']['role'] === 'student') ? '/student/dashboard' : (($_SESSION['user']['role'] === 'lecturer') ? '/lecturer/dashboard' : '/admin/dashboard'); ?>">Dashboard</a></li>

                <?php if ($_SESSION['user']['role'] === 'student'): ?>
                    <li><a href="/student/view-responses" title="View your messages">My Messages</a></li>
                    <li><a href="/student/send-message" title="Send a new message">Send Message</a></li>
                <?php elseif ($_SESSION['user']['role'] === 'lecturer'): ?>
                    <li><a href="/lecturer/dashboard" title="View your courses">My Courses</a></li>
                    <li><a href="/lecturer/read-messages" title="Read messages">Messages</a></li>
                <?php elseif ($_SESSION['user']['role'] === 'admin'): ?>
                    <li><a href="/admin/manage-users" title="Manage system users">Manage Users</a></li>
                    <li><a href="/admin/manage-messages" title="Manage system messages">Manage Messages</a></li>
                    <li><a href="/admin/reports" title="View system reports">View Reports</a></li>
                <?php endif; ?>

                <li><a href="/auth/logout" class="logout-link" title="Log out from your account">Logout</a></li>
            <?php else: ?>
                <!-- Links for guests -->
                <li><a href="/auth/login" title="Login to your account">Login</a></li>
                <li><a href="/auth/register" title="Register a new account">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
