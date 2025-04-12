<?php
use managers\SessionManager;
use managers\ApiManager;

$sessionManager = new SessionManager();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'lecturer') {
    header('Location: /auth/login');
    exit;
}

// Get the lecturer's name for display
$lecturerName = $_SESSION['user']['name'] ?? 'Lecturer';

// Fetch courses and messages from the database
$courses = [];
$messages = [];
$errorMessage = '';
require_once __DIR__ . '/../../config/Database.php';
try {
   
    $db = new \db\Database();
    $pdo = $db->getConnection();

    $lecturerId = $_SESSION['user']['id'];

    // Fetch courses assigned to the lecturer
    $stmtCourses = $pdo->prepare("
         SELECT id, code, name, pin_code
         FROM courses 
         WHERE lecturer_id = :lecturer_id
    ");
    $stmtCourses->execute([':lecturer_id' => $lecturerId]);
    $courses = $stmtCourses->fetchAll();

    // Fetch messages sent to the lecturer's courses
    $stmtMessages = $pdo->prepare("
        SELECT m.id, m.content, c.name AS course_name 
        FROM messages m
        JOIN courses c ON m.course_id = c.id
        WHERE c.lecturer_id = :lecturer_id
        ORDER BY m.created_at DESC
    ");
    $stmtMessages->execute([':lecturer_id' => $lecturerId]);
    $messages = $stmtMessages->fetchAll();
} catch (Exception $e) {
    $errorMessage = 'Failed to load courses and messages. Please try again later.';
    Logger::error('Error fetching lecturer dashboard data: ' . $e->getMessage());
}

?>

<?php include __DIR__ . '/../partials/header.php'; ?>
<?php include __DIR__ . '/../partials/navbar.php'; ?>

<div class="container">
    <h1>Welcome, <?php echo htmlspecialchars($lecturerName, ENT_QUOTES, 'UTF-8'); ?>!</h1>
    <p>This is your courses. Here you can manage your courses</p>

    <!-- Error Message -->
    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <!-- Courses Section -->
    <section>
        <h2>Your Courses</h2>
        <?php if (empty($courses)): ?>
            <p>No courses found.</p>
        <?php else: ?>
            <div id="courses-container">
                <?php foreach ($courses as $course): ?>
                    <div class="course-item">
                        <p><strong>Course Code:</strong> <?php echo htmlspecialchars($course['code'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Course Name:</strong> <?php echo htmlspecialchars($course['name'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Pin code:</strong> <?php echo htmlspecialchars($course['pin_code'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <a href="/lecturer/read-messages?course_id=<?php echo htmlspecialchars($course['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary">View Messages</a>
                        <hr>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>


<?php include __DIR__ . '/../partials/footer.php'; ?>
