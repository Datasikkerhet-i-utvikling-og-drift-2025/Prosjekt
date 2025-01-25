<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Feedback System</title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Include your CSS -->
</head>
<body>
<div class="container">
    <h1>Register</h1>

    <!-- Error Message Placeholder -->
    <div id="error-message" style="color: red; display: none;"></div>

    <!-- Registration Form -->
    <form id="register-form" action="/auth/register" method="POST">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" placeholder="Enter your full name" required>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Create a password" required>
        </div>

        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role" required>
                <option value="" disabled selected>Select your role</option>
                <option value="student">Student</option>
                <option value="lecturer">Lecturer</option>
            </select>
        </div>

        <!-- Role-Specific Fields -->
        <div id="student-fields" style="display: none;">
            <div class="form-group">
                <label for="study_program">Study Program</label>
                <input type="text" id="study_program" name="study_program" placeholder="Enter your study program">
            </div>

            <div class="form-group">
                <label for="study_year">Study Year</label>
                <input type="number" id="study_year" name="study_year" placeholder="Enter your study year">
            </div>
        </div>

        <button type="submit">Register</button>
    </form>

    <!-- Link to Login -->
    <p>Already have an account? <a href="/index.php">Login here</a>.</p>
</div>

<script>
    // Handle role-specific fields
    const roleSelect = document.getElementById('role');
    const studentFields = document.getElementById('student-fields');

    roleSelect.addEventListener('change', () => {
        if (roleSelect.value === 'student') {
            studentFields.style.display = 'block';
        } else {
            studentFields.style.display = 'none';
        }
    });

    // Handle form submission
    const form = document.getElementById('register-form');
    form.addEventListener('submit', async (e) => {
        e.preventDefault(); // Prevent default form submission

        const formData = {
            name: document.getElementById('name').value,
            email: document.getElementById('email').value,
            password: document.getElementById('password').value,
            role: document.getElementById('role').value,
        };

        if (formData.role === 'student') {
            formData.study_program = document.getElementById('study_program').value;
            formData.study_year = document.getElementById('study_year').value;
        }

        try {
            const response = await fetch('/auth/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData),
            });

            const result = await response.json();

            if (response.ok) {
                alert('Registration successful! You can now log in.');
                window.location.href = '/index.php'; // Redirect to login page
            } else {
                const errorMessage = document.getElementById('error-message');
                errorMessage.textContent = result.message || 'An error occurred during registration.';
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
