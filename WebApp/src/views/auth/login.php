<?php include '../src/views/partials/header.php'; ?>

<div class="container">
    <h1>Login</h1>

    <!-- Error Message Placeholder -->
    <?php if (!empty($_GET['error'])): ?>
        <div id="error-message" style="color: red;">
            <?= htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <!-- Login Form -->
    <form action="/auth/login" method="POST">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </div>

        <button type="submit">Login</button>
    </form>

    <!-- Link to Registration -->
    <p>Don't have an account? <a href="/register">Register here</a>.</p>
</div>

<?php include '../src/views/partials/footer.php'; ?>
