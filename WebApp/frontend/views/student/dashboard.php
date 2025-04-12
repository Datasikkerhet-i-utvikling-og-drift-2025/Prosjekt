<?php
use managers\ApiManager;
session_start();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    //Logger::info("Unauthorized access attempt to student dashboard. Session data: " . var_export($_SESSION, true));
    header('Location: /');
    exit;
}

// Get the student's name for display
$studentName = $_SESSION['user']['first_name'] ?? 'Student';
$apiManager = new ApiManager();

    // Fetch student messages
    $messages = $apiManager->post('/api/v1/student/getMessages', [
        'studentId' => $_SESSION['user']['id'],
    ]);

    // Fetch available courses
    $courses = $apiManager->get('/api/v1/student/courses');

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
            <?php if (!empty($messages['data'])): ?>
                <?php foreach ($messages as $message): ?>
                    <div class="message-item">
                        <p><strong>Course:</strong> <?php echo htmlspecialchars($courseMap[$message['course_id']] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Message:</strong> <?php echo htmlspecialchars($message['content'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>Lecturer's reply:</strong> <?php echo htmlspecialchars($message['reply'] ?? 'No reply yet', ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>Guest comments:</strong></p>
                                <?php foreach ($apiManager->post('/api/v1/guest/messages/getComments', ['messageId' => $message['id']]) as $comment): ?>
                                        <div class="guest-item">
                                            <p><strong>Guest name: </strong> <?php echo htmlspecialchars($comment['guest_name'] != '' ? $comment['guest_name'] : 'Anonym', ENT_QUOTES, 'UTF-8'); ?></p>
                                            <p> <strong>comment: </strong> <?php echo htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        </div>
                                <?php endforeach; ?>
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
            <?php if (!empty($courses['data'])): ?>
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