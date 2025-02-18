<?php
session_start();
require_once __DIR__ . '/../../config/versionURL.php';
// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ' .API_BASE_URL. '/auth/login');
    exit;
}

// Get the admin's name for display
$adminName = $_SESSION['user']['name'] ?? 'Administrator';

// Include required files using __DIR__
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
?>

<div class="container">
    <h1>Welcome, <?= htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8') ?>!</h1>
    <p>This is your dashboard. Here you can manage users, messages, and reports efficiently.</p>

    <!-- Error Message Placeholder -->
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8') ?>
            <?php unset($_SESSION['error_message']); // Clear error message after displaying ?>
        </div>
    <?php endif; ?>

    <!-- Admin Actions -->
    <div class="admin-actions">
        <section class="action-section">
            <h2>Manage Users</h2>
            <p>View, edit, or remove users in the system.</p>
            <a href="<?= APP_BASE_URL ?>/admin/manage-users" class="btn btn-primary">Manage Users</a>
        </section>

        <section class="action-section">
            <h2>Manage Messages</h2>
            <p>View all messages, respond to them, or delete inappropriate ones.</p>
            <a href="<?= APP_BASE_URL ?>/admin/manage-messages" class="btn btn-primary">Manage Messages</a>
        </section>

        <section class="action-section">
            <h2>View Reports</h2>
            <p>Review and resolve reports on inappropriate messages.</p>
            <a href="<?= APP_BASE_URL ?>/admin/reports" class="btn btn-primary">View Reports</a>
        </section>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?> <!-- Include Footer -->
