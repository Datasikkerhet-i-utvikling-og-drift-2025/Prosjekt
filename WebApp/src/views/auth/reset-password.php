<?php include '../src/views/partials/header.php'; ?>

<div class="container">
    <h1>Reset Your Password</h1>

    <!-- Error or Success Message -->
    <?php if (!empty($_GET['error'])): ?>
        <div id="error-message" style="color: red;">
            <?= htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php elseif (!empty($_GET['success'])): ?>
        <div id="success-message" style="color: green;">
            <?= htmlspecialchars($_GET['success'], ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <!-- Password Reset Form -->
    <form action="/auth/password-reset" method="POST">
        <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />

        <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password" placeholder="Enter your new password" required>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your new password" required>
        </div>

        <button type="submit">Reset Password</button>
    </form>

    <!-- Link to Login -->
    <p>Remembered your password? <a href="/">Login here</a>.</p>
</div>

<?php include '../src/views/partials/footer.php'; ?>
