<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="form-container">
    <h1>Reset Password</h1>

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

    <?php if (empty($_GET['token'])): ?>
    <!-- Request Reset Form -->
    <form action="/auth/password-reset/request" method="POST" class="form">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" 
                   placeholder="Enter your email address" required>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Request Password Reset</button>
        </div>
    </form>
<?php else: ?>


    <!-- Password Reset Form -->
    <form action="/auth/password-reset" method="POST" class="form">
        <input
                type="hidden"
                name="token"
                value="<?= htmlspecialchars($_GET['token'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />

        <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password" required>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Reset Password</button>
        </div>
    </form>

    <p>Remember your password? <a href="/">Login here</a></p>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../partials/footer.php'; ?>