<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="form-container">
    <h1>Login</h1>

    <!-- Error Message Placeholder -->
    <?php if (!empty($_GET['error'])): ?>
        <div id="error-message" class="error">
            <?= htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <!-- Login Form -->
    <form action="/auth/login" method="POST" class="form">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter your email"
                    required
                    value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    required>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Login</button>
            <a href="/register" class="btn btn-secondary">Register</a>
            <a href="/guest/messages" class="btn btn-guest">Continue as Guest</a>
        </div>
    </form>

    <!-- Forgot Password Link -->
    <div class="forgot-password">
        <p><a href="/reset-password">Forgot your password?</a></p>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
