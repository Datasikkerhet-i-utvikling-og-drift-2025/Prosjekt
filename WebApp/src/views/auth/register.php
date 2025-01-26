<?php include '../src/views/partials/header.php'; ?>

<div class="container">
    <h1>Register</h1>

    <!-- Error Message Placeholder -->
    <?php if (!empty($_GET['error'])): ?>
        <div id="error-message" style="color: red;">
            <?= htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <!-- Registration Form -->
    <form action="/auth/register" method="POST">
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
            <select id="role" name="role" onchange="this.form.submit()" required>
                <option value="" disabled selected>Select your role</option>
                <option value="student" <?= (isset($_POST['role']) && $_POST['role'] === 'student') ? 'selected' : '' ?>>Student</option>
                <option value="lecturer" <?= (isset($_POST['role']) && $_POST['role'] === 'lecturer') ? 'selected' : '' ?>>Lecturer</option>
            </select>
        </div>

        <!-- Role-Specific Fields -->
        <?php if (!empty($_POST['role']) && $_POST['role'] === 'student'): ?>
            <div class="form-group">
                <label for="study_program">Study Program</label>
                <input type="text" id="study_program" name="study_program" placeholder="Enter your study program" required>
            </div>

            <div class="form-group">
                <label for="study_year">Study Year</label>
                <input type="number" id="study_year" name="study_year" placeholder="Enter your study year" required>
            </div>
        <?php endif; ?>

        <button type="submit">Register</button>
    </form>

    <!-- Link to Login -->
    <p>Already have an account? <a href="/">Login here</a>.</p>
</div>

<?php include '../src/views/partials/footer.php'; ?>
