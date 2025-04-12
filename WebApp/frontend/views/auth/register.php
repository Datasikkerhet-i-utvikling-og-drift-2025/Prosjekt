<?php

use managers\SessionManager;
use managers\ApiManager;

$sessionManager = new SessionManager(); // erstatter session_start()

if ($sessionManager->isAuthenticated()) {
    $role = $sessionManager->getUserRole();
    header("Location: /$role/dashboard");
    exit;
}

try {
    $apiManager = new ApiManager();
} catch (Throwable $e) {
    $_SESSION['errors'] = ['Cannot initialize ApiManager: ' . $e->getMessage()];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $responseData = $apiManager->post('/api/v1/auth/register', $_POST);

        if ($responseData['success'] === true) {
            $_SESSION['success'] = $responseData['data']['message'] ?? 'Registration successful!';
            header('Location: /');
            exit;
        } else {
            $_SESSION['errors'] = $responseData['errors'] ?? ['An unexpected error occurred.'];
        }
    } catch (Throwable $e) {
        $_SESSION['errors'] = ['Unexpected error: ' . $e->getMessage()];
    }
}

require_once __DIR__ . '/../partials/header.php';
?>

<div class="form-container">
    <h1>Register</h1>

    <?php if (!empty($_SESSION['errors'])): ?>
        <div id="error-message" class="error">
            <ul>
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <div id="success-message" class="success">
            <?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" placeholder="Tom Heine" value="<?= htmlspecialchars($_POST['firstName'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-group">
            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" placeholder="Nätt" value="<?= htmlspecialchars($_POST['lastName'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="tom.h.natt@hiof.no" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Password123!" required>
        </div>

        <div class="form-group">
            <label for="repeat_password">Repeat Password</label>
            <input type="password" id="repeat_password" name="repeat_password" placeholder="Password123!" required>
        </div>

        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role" required>
                <option value="" disabled <?= empty($_POST['role']) ? 'selected' : '' ?>>Select your role</option>
                <option value="student" <?= ($_POST['role'] ?? '') === 'student' ? 'selected' : '' ?>>Student</option>
                <option value="lecturer" <?= ($_POST['role'] ?? '') === 'lecturer' ? 'selected' : '' ?>>Lecturer</option>
            </select>
        </div>

        <div id="student-fields" style="display: <?= ($_POST['role'] ?? '') === 'student' ? 'block' : 'none' ?>;">
            <div class="form-group">
                <label for="study_program">Study Program</label>
                <input type="text" id="study_program" name="study_program" placeholder="Information Systems" value="<?= htmlspecialchars($_POST['study_program'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="form-group">
                <label for="enrollment_year">Cohort Year</label>
                <input type="number" id="enrollment_year" name="enrollment_year" placeholder="2025" value="<?= htmlspecialchars($_POST['enrollmentYear'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
        </div>
        <div id="lecturer-fields" style="display: <?= ($_POST['role'] ?? '') === 'lecturer' ? 'block' : 'none' ?>;">
    <div class="form-group">
        <label for="profile_picture">Profile Picture</label>
        <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
    </div>

    <div class="form-group">
        <label for="course_code">Course Code</label>
        <input type="text" id="course_code" name="courseCode"
               placeholder="ITF12345"
               value="<?= htmlspecialchars($_POST['course_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
    </div>

    <div class="form-group">
        <label for="course_name">Course Name</label>
        <input type="text" id="course_name" name="course_name"
               placeholder="Datasikkerhet i utvikling og drift"
               value="<?= htmlspecialchars($_POST['course_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
    </div>

    <div class="form-group">
        <label for="coursePin">Course PIN</label>
        <input type="text" id="course_pin" name="course_pin"
               placeholder="1337"
               pattern="[0-9]{4}"
               title="Please enter a 4-digit PIN code"
               value="<?= htmlspecialchars($_POST['course_pin'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
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

        const studentFields = document.querySelectorAll('#student-fields input');
        const lecturerFields = document.querySelectorAll('#lecturer-fields input');

    if (role === 'student') {
        studentFields.forEach(field => field.setAttribute('required', 'true'));
        lecturerFields.forEach(field => field.removeAttribute('required'));
    } else if (role === 'lecturer') {
        lecturerFields.forEach(field => field.setAttribute('required', 'true'));
        studentFields.forEach(field => field.removeAttribute('required'));
    }
    });

    // Funksjon for å vise feilmelding
    function showError(input, message) {
        // Fjern eksisterende feilmelding hvis den finnes
        const existingError = input.parentElement.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }

        // Opprett og vis ny feilmelding
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.style.color = 'red';
        errorDiv.style.fontSize = '0.8em';
        errorDiv.style.marginTop = '5px';
        errorDiv.textContent = message;
        input.parentElement.appendChild(errorDiv);
    }

    // Funksjon for å fjerne feilmelding
    function removeError(input) {
        const errorDiv = input.parentElement.querySelector('.error-message');
        if (errorDiv) {
            errorDiv.remove();
        }
    }

    // Valider passordet mens brukeren skriver
    document.getElementById('password').addEventListener('input', function() {
        if (this.value.length < 8) {
            showError(this, 'Password must be at least 8 characters long');
        } else {
            removeError(this);
        }
    });

    // getElementById repeat_password samsvarte ikke med id på linje 82
    document.getElementById('repeatPassword').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const repeatPassword = this.value;
        if (repeatPassword !== password) {
            showError(this, 'Passwords must match');
        } else {
            removeError(this);
        }
    });

    // Valider fornavn mens brukeren skriver
    // getElementById first_name samsvarte ikke med id på linje 62
    document.getElementById('first_name').addEventListener('input', function() {
        if (this.value.length < 3) {
            showError(this, 'First name must be at least 3 characters long');
        } else {
            removeError(this);
        }
    });

    // Valider etternavn mens brukeren skriver
    // getElementById last_name samsvarte ikke med id på linje 66
    document.getElementById('last_name').addEventListener('input', function() {
        if (this.value.length < 3) {
            showError(this, 'Last name must be at least 3 characters long');
        } else {
            removeError(this);
        }
    });

    // Valider e-post mens brukeren skriver
    document.getElementById('email').addEventListener('input', function() {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(this.value)) {
            showError(this, 'Email must be a valid email address (e.g., user@example.com)');
        } else {
            removeError(this);
        }
    });

    // Valider kurskode mens brukeren skriver
    // getElementById course_code samsvarte ikke med id på linje 113
    document.getElementById('course_code').addEventListener('input', function() {
        if (this.value.length > 10) {
            showError(this, 'Course code must not exceed 10 characters');
        } else {
            removeError(this);
        }
    });

    // Valider før innsending
    // retter opp id referenaser til input-feltene i skjemaet
    document.querySelector('form').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const repeatPassword = document.getElementById('repeat_password').value;
        const firstName = document.getElementById('first_name').value;
        const lastName = document.getElementById('last_name').value;
        const email = document.getElementById('email').value;
        const courseCode = document.getElementById('course_code').value;

        if (password.length < 8) {
            e.preventDefault();
            showError(document.getElementById('password'), 'Password must be at least 8 characters long');
            return false;
        }

        if (repeatPassword !== password) {
            e.preventDefault();
            showError(document.getElementById('repeat_password'), 'Passwords must match');
            return false;
        }

        if (firstName.length < 3) {
            e.preventDefault();
            showError(document.getElementById('first_name'), 'First name must be at least 3 characters long');
            return false;
        }

        if (lastName.length < 3) {
            e.preventDefault();
            showError(document.getElementById('last_name'), 'Last name must be at least 3 characters long');
            return false;
        }

        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
            e.preventDefault();
            showError(document.getElementById('email'), 'Email must contain @ and end with .com (e.g., user@example.com)');
            return false;
        }

        if (courseCode.length > 10) {
            e.preventDefault();
            showError(document.getElementById('course_code'), 'Course code must not exceed 10 characters');
            return false;
        }
    });
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>