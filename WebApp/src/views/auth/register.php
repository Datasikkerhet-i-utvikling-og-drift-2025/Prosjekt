<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="container">
    <h1>Register</h1>

    <?php if (!empty($_GET['error'])): ?>
        <div id="error-message" class="error">
            <?= htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form action="/auth/register" method="POST" id="registerForm">
        <div class="form-group">
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" required>
        </div>

        <div class="form-group">
            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" required>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="form-group">
            <label for="repeat_password">Repeat Password</label>
            <input type="password" id="repeat_password" name="repeat_password" required>
        </div>

        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role" required>
                <option value="" disabled selected>Select your role</option>
                <option value="student">Student</option>
                <option value="lecturer">Lecturer</option>
            </select>
        </div>

        <div id="student-fields" style="display: none;">
            <div class="form-group">
                <label for="study_program">Study Program</label>
                <input type="text" id="study_program" name="study_program">
            </div>

            <div class="form-group">
                <label for="cohort_year">Cohort Year</label>
                <input type="number" id="cohort_year" name="cohort_year">
            </div>
        </div>

        <div id="lecturer-fields" style="display: none;">
            <div class="form-group">
                <label for="profile_picture">Profile Picture</label>
                <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Register</button>
        </div>
    </form>

    <p>Already have an account? <a href="/">Login here</a>.</p>
</div>

<script>
    document.getElementById('role').addEventListener('change', function () {
        const role = this.value;
        document.getElementById('student-fields').style.display = role === 'student' ? 'block' : 'none';
        document.getElementById('lecturer-fields').style.display = role === 'lecturer' ? 'block' : 'none';
    });

    document.getElementById('registerForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const password = document.getElementById('password').value;
        const repeatPassword = document.getElementById('repeat_password').value;

        if (password !== repeatPassword) {
            alert('Passwords do not match');
            return;
        }

        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        fetch(this.action, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data),
        })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert(data.message || 'Registration failed');
                } else {
                    alert('Registration successful');
                }
            })
            .catch(console.error);
    });

    document.getElementById('role').dispatchEvent(new Event('change'));
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
