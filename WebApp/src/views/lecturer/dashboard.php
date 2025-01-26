<?php
session_start();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'lecturer') {
    header('Location: /auth/login.php');
    exit;
}

// Get the lecturer's name for display
$lecturerName = $_SESSION['user']['name'] ?? 'Lecturer';

// Fetch courses and messages from the database
$courses = [];
$messages = [];
$errorMessage = '';

try {
    require_once '../../helpers/Database.php';

    $db = new \db\Database();
    $pdo = $db->getConnection();

    $lecturerId = $_SESSION['user']['id'];

    // Fetch courses assigned to the lecturer
    $stmt = $pdo->prepare("
        SELECT id, code, name 
        FROM courses 
        WHERE lecturer_id = :lecturer_id
    ");
    $stmt->execute([':lecturer_id' => $lecturerId]);
    $courses = $stmt->fetchAll();

    // Fetch messages sent to the lecturer's courses
    $stmt = $pdo->prepare("
        SELECT m.id, m.content, c.name AS course_name 
        FROM messages m
        JOIN courses c ON m.course_id = c.id
        WHERE c.lecturer_id = :lecturer_id
        ORDER BY m.created_at DESC
    ");
    $stmt->execute([':lecturer_id' => $lecturerId]);
    $messages = $stmt->fetchAll();
} catch (Exception $e) {
    $errorMessage = 'Failed to load courses and messages. Please try again later.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Dashboard - Feedback System</title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Include your CSS -->
</head>
<body>
<?php include '../partials/navbar.php'; ?> <!-- Include Navbar -->

<div class="container">
    <h1>Welcome, <?php echo htmlspecialchars($lecturerName, ENT_QUOTES, 'UTF-8'); ?>!</h1>
    <p>This is your dashboard. Here you can manage your courses and view messages from students.</p>

    <!-- Error Message Placeholder -->
    <?php if (!empty($errorMessage)): ?>
        <div style="color: red;"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
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
                        <a href="/lecturer/read-messages.php?course_id=<?php echo htmlspecialchars($course['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn">View Messages</a>
                        <hr>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- Messages Section -->
    <section>
        <h2>Student Messages</h2>
        <?php if (empty($messages)): ?>
            <p>No messages found.</p>
        <?php else: ?>
            <div id="messages-container">
                <?php foreach ($messages as $message): ?>
                    <div class="message-item">
                        <p><strong>Message:</strong> <?php echo htmlspecialchars($message['content'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Course:</strong> <?php echo htmlspecialchars($message['course_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <a href="/lecturer/reply.php?message_id=<?php echo htmlspecialchars($message['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn">Reply</a>
                        <hr>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php include '../partials/footer.php'; ?> <!-- Include Footer -->
</body>
</html>
