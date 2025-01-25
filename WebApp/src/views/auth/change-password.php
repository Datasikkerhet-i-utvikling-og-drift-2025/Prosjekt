<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Feedback System</title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Include your CSS -->
</head>
<body>
<div class="container">
    <h1>Change Password</h1>

    <!-- Error Message Placeholder -->
    <div id="error-message" style="color: red; display: none;"></div>
    <div id="success-message" style="color: green; display: none;"></div>

    <!-- Change Password Form -->
    <form id="change-password-form" action="/auth/change-password" method="POST">
        <div class="form-group">
            <label for="current_password">Current Password</label>
            <input type="password" id="current_password" name="current_password" placeholder="Enter your current password" required>
        </div>

        <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password" placeholder="Enter your new password" required>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your new password" required>
        </div>

        <button type="submit">Change Password</button>
    </form>
</div>

<script>
    // Handle form submission
    const form = document.getElementById('change-password-form');
    form.addEventListener('submit', async (e) => {
        e.preventDefault(); // Prevent default form submission

        const currentPassword = document.getElementById('current_password').value;
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        // Validate new passwords match
        if (newPassword !== confirmPassword) {
            const errorMessage = document.getElementById('error-message');
            errorMessage.textContent = 'New passwords do not match.';
            errorMessage.style.display = 'block';
            return;
        }

        try {
            const response = await fetch('/auth/change-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + localStorage.getItem('token') // Assuming you're using JWT
                },
                body: JSON.stringify({ current_password: currentPassword, new_password: newPassword }),
            });

            const result = await response.json();

            if (response.ok) {
                const successMessage = document.getElementById('success-message');
                successMessage.textContent = 'Password changed successfully!';
                successMessage.style.display = 'block';

                // Clear the form
                form.reset();
            } else {
                const errorMessage = document.getElementById('error-message');
                errorMessage.textContent = result.message || 'An error occurred while changing your password.';
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
