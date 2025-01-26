<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="container">
    <h1>Register</h1>

    <!-- Error Message Placeholder -->
    <?php if (!empty($_GET['error'])): ?>
        <div id="error-message" class="error">
            <?= htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <!-- Registration Form -->
    <form action="/auth/register" method="POST" enctype="multipart/form-data" class="form">
        <div class="form-group">
            <label for="first_name">First Name</label>
            <input
                    type="text"
                    id="first_name"
                    name="first_name"
                    placeholder="Enter your first name"
                    required
                    value="<?= htmlspecialchars($_POST['first_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="form-group">
            <label for="last_name">Last Name</label>
            <input
                    type="text"
                    id="last_name"
                    name="last_name"
                    placeholder="Enter your last name"
                    required
                    value="<?= htmlspecialchars($_POST['last_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

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
                    placeholder="Create a password"
                    required>
        </div>

        <div class="form-group">
            <label for="repeat_password">Repeat Password</label>
            <input
                    type="password"
                    id="repeat_password"
                    name="repeat_password"
                    placeholder="Repeat your password"
                    required>
        </div>

        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role" required>
                <option value="" disabled selected>Select your role</option>
                <option value="student" <?= (isset($_POST['role']) && $_POST['role'] === 'student') ? 'selected' : '' ?>>Student</option>
                <option value="lecturer" <?= (isset($_POST['role']) && $_POST['role'] === 'lecturer') ? 'selected' : '' ?>>Lecturer</option>
            </select>
        </div>

        <!-- Student-Specific Fields -->
        <div id="student-fields" style="display: none;">
            <div class="form-group">
                <label for="study_program">Study Program</label>
                <input
                        type="text"
                        id="study_program"
                        name="study_program"
                        placeholder="Enter your study program"
                        value="<?= htmlspecialchars($_POST['study_program'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form-group">
                <label for="cohort_year">Cohort Year</label>
                <input
                        type="number"
                        id="cohort_year"
                        name="cohort_year"
                        placeholder="Enter your cohort year"
                        value="<?= htmlspecialchars($_POST['cohort_year'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
        </div>

        <!-- Lecturer-Specific Fields -->
        <div id="lecturer-fields" style="display: none;">
            <div class="form-group">
                <label for="profile_picture">Profile Picture</label>
                <input
                        type="file"
                        id="profile_picture"
                        name="profile_picture"
                        accept="image/*">
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Register</button>
        </div>
    </form>

    <!-- Link to Login -->
    <p>Already have an account? <a href="/">Login here</a>.</p>
</div>

<script>
    // Dynamically show/hide fields based on the selected role
    document.getElementById('role').addEventListener('change', function () {
        const role = this.value;

        // Get the field containers
        const studentFields = document.getElementById('student-fields');
        const lecturerFields = document.getElementById('lecturer-fields');

        // Hide all role-specific fields by default
        studentFields.style.display = 'none';
        lecturerFields.style.display = 'none';

        // Show the relevant fields
        if (role === 'student') {
            studentFields.style.display = 'block';
        } else if (role === 'lecturer') {
            lecturerFields.style.display = 'block';
        }
    });

    document.getElementById('registerForm').addEventListener('submit', function (e) {
        const role = document.getElementById('role').value;

        if (role !== 'student') {
            document.getElementById('study_program').value = '';
            document.getElementById('cohort_year').value = '';
        }
    });


    // Trigger change event on page load to handle preselected role
    document.getElementById('role').dispatchEvent(new Event('change'));
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
