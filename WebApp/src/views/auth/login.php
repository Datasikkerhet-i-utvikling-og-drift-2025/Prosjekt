<?php
session_start();
if (isset($_SESSION['user'])) {
    $role = $_SESSION['user']['role'] ?? '';
    if ($role === 'student') {
        header('Location: /student/dashboard');
        exit;
    } elseif ($role === 'lecturer') {
        header('Location: /lecturer/dashboard');
        exit;
    } elseif ($role === 'admin') {
        header('Location: /admin/dashboard');
        exit;
    } 
}
?>

<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="form-container">
    <h1>Login</h1>

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
        </div>
    </form>

    <form action="/guests/dashboard" method="POST" class="form">
        <div class="form-actions">
            <button type="submit" class="btn btn-guest">Continue as Guest</button>
        </div>
    </form>

    <!-- Forgot Password Link -->
    <div class="additional-links">
    <p><a href="/reset-password">Forgot your password?</a></p>
</div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
