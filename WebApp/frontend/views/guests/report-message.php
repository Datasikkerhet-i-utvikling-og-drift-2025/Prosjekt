<?php
use managers\SessionManager;
use managers\ApiManager;

$sessionManager = new SessionManager();

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';

// Sanitize output
function sanitize($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Initialize variables
$courses = [];
$errorMessage = '';

try {
    // Fetch available courses
    $db = new service\DatabaseService();
    $pdo = $db->pdo;

    $stmtCourses = $pdo->query("SELECT id, code, name FROM courses");
    $courses = $stmtCourses->fetchAll();
} catch (Exception $e) {
    $errorMessage = 'Failed to load courses. Please try again later.';
}
?>

<div class="container">
    <h1>Guest Dashboard</h1>
    <p>Welcome to the guest dashboard. Here you can view messages for courses you have access to.</p>

    <!-- Error Message -->
    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger"><?php echo sanitize($errorMessage); ?></div>
    <?php endif; ?>

    <!-- Courses Section -->
    <section class="courses-section">
        <h2>Available Courses</h2>
        <div id="courses-container">
            <?php if (!empty($courses)): ?>
                <?php foreach ($courses as $course): ?>
                    <div class="course-item">
                        <p><strong>Course Code:</strong> <?php echo sanitize($course['code']); ?></p>
                        <p><strong>Course Name:</strong> <?php echo sanitize($course['name']); ?></p>
                        <form action="/guest/view-messages.php" method="get">
                            <input type="hidden" name="course_code" value="<?php echo sanitize($course['code']); ?>">
                            <div class="form-group">
                                <label for="pin_code">Enter PIN Code:</label>
                                <input type="text" id="pin_code" name="pin_code" required>
                            </div>
                            <button type="submit" class="btn btn-primary">View Messages</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No courses found.</p>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>