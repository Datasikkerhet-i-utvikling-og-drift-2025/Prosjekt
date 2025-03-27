<?php
use managers\ApiManager;

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

$apiToken = getenv('API_TOKEN');
$apiManager = new ApiManager($apiToken);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $responseData = $apiManager->post('/api/v1/auth/register', $_POST);

    if ($responseData['success']) {
        $_SESSION['success'] = $responseData['message'];
        header('Location: /');
        exit;
    } else {
        $_SESSION['errors'] = $responseData['errors'] ?? ['An unexpected error occurred.'];
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
            <label for="firstName">First Name</label>
            <input type="text" id="firstName" name="firstName" placeholder="Tom Heine" value="<?= htmlspecialchars($_POST['first_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-group">
            <label for="last_name">Last Name</label>
            <input type="text" id="lastName" name="lastName" placeholder="Nätt" value="<?= htmlspecialchars($_POST['last_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
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
            <label for="repeatPassword">Repeat Password</label>
            <input type="password" id="repeatPassword" name="repeatPassword" placeholder="Password123!" required>
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
                <label for="studyProgram">Study Program</label>
                <input type="text" id="studyProgram" name="studyProgram" placeholder="Information Systems" value="<?= htmlspecialchars($_POST['study_program'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="form-group">
                <label for="enrollmentYear">Cohort Year</label>
                <input type="number" id="enrollmentYear" name="enrollmentYear" placeholder="2025" value="<?= htmlspecialchars($_POST['cohort_year'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
        </div>
        <div id="lecturer-fields" style="display: <?= ($_POST['role'] ?? '') === 'lecturer' ? 'block' : 'none' ?>;">
    <div class="form-group">
        <label for="profilePicture">Profile Picture</label>
        <input type="file" id="profilePicture" name="profilePicture" accept="image/*">
    </div>
    
    <div class="form-group">
        <label for="course_code">Course Code</label>
        <input type="text" id="courseCode" name="courseCode"
               placeholder="ITF12345" 
               value="<?= htmlspecialchars($_POST['courseCode'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
    </div>

    <div class="form-group">
        <label for="course_name">Course Name</label>
        <input type="text" id="courseName" name="courseName"
               placeholder="Datasikkerhet i utvikling og drift" 
               value="<?= htmlspecialchars($_POST['courseName'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
    </div>

    <div class="form-group">
        <label for="coursePin">Course PIN</label>
        <input type="text" id="coursePin" name="coursePin"
               placeholder="1337" 
               pattern="[0-9]{4}" 
               title="Please enter a 4-digit PIN code"
               value="<?= htmlspecialchars($_POST['coursePin'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
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

    document.getElementById('repeat_password').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const repeatPassword = this.value;
        if (repeatPassword !== password) {
            showError(this, 'Passwords must match');
        } else {
            removeError(this);
        }
    });

    // Valider fornavn mens brukeren skriver
    document.getElementById('first_name').addEventListener('input', function() {
        if (this.value.length < 3) {
            showError(this, 'First name must be at least 3 characters long');
        } else {
            removeError(this);
        }
    });

    // Valider etternavn mens brukeren skriver
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
    document.getElementById('course_code').addEventListener('input', function() {
        if (this.value.length > 10) {
            showError(this, 'Course code must not exceed 10 characters');
        } else {
            removeError(this);
        }
    });

    // Valider før innsending
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