<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="form-container">
    <h1>Reset Your Password</h1>

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
        <!-- Steg 1: Be om email -->
        <form action="/auth/password-reset/request" method="POST" class="form">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="Enter your email"
                    required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Request Password Reset</button>
            </div>
        </form>
    <?php else: ?>
        <!-- Steg 2: Reset passordet med token -->
        <form action="/auth/password-reset" method="POST" class="form">
            <input 
                type="hidden" 
                name="token" 
                value="<?= htmlspecialchars($_GET['token'], ENT_QUOTES, 'UTF-8') ?>">

            <div class="form-group">
                <label for="new_password">New Password</label>
                <input 
                    type="password" 
                    id="new_password" 
                    name="new_password" 
                    placeholder="Enter your new password"
                    required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    placeholder="Confirm your new password"
                    required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Reset Password</button>
            </div>
        </form>
    <?php endif; ?>

    <p>Remember your password? <a href="/login">Login here</a></p>
</div>

<!-- Legg til klient-side validering -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');
            
            if (newPassword && confirmPassword) {
                if (newPassword.value.length < 8) {
                    e.preventDefault();
                    alert('Password must be at least 8 characters long');
                    return;
                }
                
                if (newPassword.value !== confirmPassword.value) {
                    e.preventDefault();
                    alert('Passwords do not match');
                    return;
                }
            }
        });
    }
});
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>