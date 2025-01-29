<?php
session_start();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header('Location: /');
    exit;
}

// Get the student's name for display
$studentName = $_SESSION['user']['name'] ?? 'Student';

// Include required files
require_once __DIR__ . '/../../helpers/Database.php';
require_once __DIR__ . '/../../helpers/Logger.php';

try {
    $db = new \db\Database();
    $pdo = $db->getConnection();

    // Fetch student messages
    $stmtMessages = $pdo->prepare("SELECT content, reply FROM messages WHERE student_id = :student_id");
    $stmtMessages->execute([':student_id' => $_SESSION['user']['id']]);
    $messages = $stmtMessages->fetchAll();

    // Fetch available courses
    $stmtCourses = $pdo->query("SELECT id, code, name FROM courses");
    $courses = $stmtCourses->fetchAll();

    Logger::info("Student dashboard loaded successfully for user ID {$_SESSION['user']['id']}.");
} catch (Exception $e) {
    $error = 'Failed to load data. Please try again later.';
    Logger::error('Error loading student dashboard: ' . $e->getMessage());
}
?>

<?php include __DIR__ . '/../partials/header.php'; ?>
<?php include __DIR__ . '/../partials/navbar.php'; ?>

<div class="container">
    <h1>Welcome, <?php echo htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8'); ?>!</h1>
    <p>This is your dashboard. Here you can view your messages and explore courses.</p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <!-- Messages Section -->
    <section class="messages-section">
        <h2>Your Messages</h2>
        <div id="messages-container">
            <?php if (!empty($messages)): ?>
                <?php foreach ($messages as $message): ?>
                    <div class="message-item">
                        <p><strong>Message:</strong> <?php echo htmlspecialchars($message['content'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Reply:</strong> <?php echo htmlspecialchars($message['reply'] ?? 'No reply yet', ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No messages found.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Courses Section -->
    <section class="courses-section">
        <h2>Available Courses</h2>
        <div id="courses-container">
            <?php if (!empty($courses)): ?>
                <?php foreach ($courses as $course): ?>
                    <div class="course-item">
                        <p><strong>Course Code:</strong> <?php echo htmlspecialchars($course['code'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Course Name:</strong> <?php echo htmlspecialchars($course['name'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <a href="/student/send-message?course_id=<?php echo $course['id']; ?>" class="btn btn-primary">Send a Message</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No courses found.</p>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
