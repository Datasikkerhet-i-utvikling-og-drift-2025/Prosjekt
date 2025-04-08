<?php
/*
session_start();
require_once __DIR__ . '/../../helpers/Logger.php';
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
$comments = [];
$errorMessage = '';
*/

use managers\ApiManager;

session_start();

require_once __DIR__ . '/../../helpers/Logger.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'lecturer') {
    header('Location: /auth/login');
    exit;
}

$lecturerName = $_SESSION['user']['name'] ?? 'Lecturer';
$messages = [];
$courses = [];
$errorMessage = '';

try {
    $apiManager = new ApiManager();

    // Example: Fetch courses first if needed (assuming you have such endpoint)
    // $coursesResponse = $apiManager->get('/api/v1/lecturer/courses');
    // $courses = $coursesResponse['data'] ?? [];

    // Fetch messages for a course
    if (isset($_GET['course_id'])) {
        $courseId = (int)$_GET['course_id'];

        $responseData = $apiManager->post('/api/v1/lecturer/messages', [
            'courseId' => $courseId
        ]);

        if ($responseData['success']) {
            $messages = $responseData['data']['messages'] ?? [];
        } else {
            $errorMessage = 'Failed to fetch messages.';
        }
    }
} catch (Throwable $e) {
    $errorMessage = 'Unexpected error: ' . $e->getMessage();
}

?>

<?php include __DIR__ . '/../partials/header.php'; ?>
<?php include __DIR__ . '/../partials/navbar.php'; ?>

<div class="container">
    <h1>Welcome, <?php echo htmlspecialchars($lecturerName, ENT_QUOTES, 'UTF-8'); ?>!</h1>
    <p>This is your dashboard. Here you can manage your courses and view messages from students.</p>

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
                        <?php foreach ($comments as $comment): ?>
                            <?php if ($comment['message_id'] === $message['id']): ?>
                                <div class="guest-item">
                                    <p><strong>Guest name: </strong> <?php echo htmlspecialchars($comment['guest_name'] != '' ? $comment['guest_name'] : 'Anonym', ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p> <strong>comment: </strong> <?php echo htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                            <?php else: ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <a href="/lecturer/reply?message_id=<?php echo htmlspecialchars($message['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-secondary">Reply</a>
                        <hr>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
    
    
</div>


<?php include __DIR__ . '/../partials/footer.php'; ?>
