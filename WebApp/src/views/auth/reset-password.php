<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Feedback System</title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Include your CSS -->
</head>
<body>
<div class="container">
    <h1>Reset Your Password</h1>

    <!-- Error Message Placeholder -->
    <div id="error-message" style="color: red; display: none;"></div>
    <div id="success-message" style="color: green; display: none;"></div>

    <!-- Password Reset Form -->
    <form id="reset-password-form" action="/auth/password-reset" method="POST">
        <input type="hidden" id="token" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />

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
    <p>Remembered your password? <a href="/auth/login.php">Login here</a>.</p>
</div>

<script>
    // Handle form submission
    const form = document.getElementById('reset-password-form');
    form.addEventListener('submit', async (e) => {
        e.preventDefault(); // Prevent default form submission

        const token = document.getElementById('token').value;
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        // Validate password match
        if (newPassword !== confirmPassword) {
            const errorMessage = document.getElementById('error-message');
            errorMessage.textContent = 'Passwords do not match.';
            errorMessage.style.display = 'block';
            return;
        }

        try {
            const response = await fetch('/auth/password-reset', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ token, new_password: newPassword }),
            });

            const result = await response.json();

            if (response.ok) {
                const successMessage = document.getElementById('success-message');
                successMessage.textContent = 'Password reset successfully! Redirecting to login...';
                successMessage.style.display = 'block';

                // Redirect to login page after 2 seconds
                setTimeout(() => {
                    window.location.href = '/auth/login.php';
                }, 2000);
            } else {
                const errorMessage = document.getElementById('error-message');
                errorMessage.textContent = result.message || 'An error occurred while resetting your password.';
                errorMessage.style.display = 'block';
            }
        } catch (error) {
            console.error('Error:', error);
            const errorMessage = document.getElementById('error-message');
            errorMessage.textContent = 'Unable to connect to the server. Please try again later.';
            errorMessage.style.display = 'block';
        }
    });
</script>
</body>
</html>
