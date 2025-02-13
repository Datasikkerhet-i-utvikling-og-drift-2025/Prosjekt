<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: /login');
    exit;
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

use helpers\Database;

$db = new Database();
$pdo = $db->pdo;
$authController = new AuthController($pdo);

// Get user data
$user = $authController->getUserById($_SESSION['user']['id']);
?>

<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="container">
    <h1>My Profile</h1>

    <?php if (!empty($_SESSION['errors'])): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($_SESSION['errors'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <div class="profile-info">
        <h2>Personal Information</h2>
        <p><strong>Name:</strong> <?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Role:</strong> <?= htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8') ?></p>
    </div>

    <div class="change-password-section">
        <h2>Change Password</h2>
        <form action="/auth/change-password" method="POST" class="form">
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>

            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn btn-primary">Change Password</button>
        </form>
    </div>
</div>

<script>
// Client-side validation
document.querySelector('form').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    if (newPassword.length < 8) {
        e.preventDefault();
        alert('Password must be at least 8 characters long');
        return;
    }

    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match');
        return;
    }
});
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>