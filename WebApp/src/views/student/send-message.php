<?php
session_start();
require_once __DIR__ . '/../../config/versionURL.php';
// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header('Location: ' .API_BASE_URL. '/auth/login');
    exit;
}

// Initialize variables
$messageSent = false;
$errorMessage = '';
$courseId = isset($_GET['course_id']) ? $_GET['course_id'] : (isset($_POST['course_id']) ? $_POST['course_id'] : '');

// Fetch available courses
require_once __DIR__ . '/../../config/Database.php';
$db = new \db\Database();
$pdo = $db->getConnection();
$stmtCourses = $pdo->query("SELECT id, code, name FROM courses");
$courses = $stmtCourses->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../../config/Database.php';

    require_once __DIR__ . '/../../helpers/Logger.php';
    require_once __DIR__ . '/../../models/Message.php';
    
    try {
        $messageContent = trim($_POST['message_content'] ?? '');
        $studentId = $_SESSION['user']['id'];
        // Generate anonymous_id (UUID)
        $anonymousId = bin2hex(random_bytes(16));

        if (empty($messageContent)) {
            $errorMessage = 'Message content cannot be empty.';
        } else {
            $messageModel = new Message($pdo);
            if ($messageModel->createMessage($studentId, $courseId, $anonymousId, $messageContent)) {
                $messageSent = true;
                Logger::info("Message sent successfully with anonymous ID");
                unset($_POST['course_id']); // Forget the course ID after sending the message
                $courseId = isset($_GET['course_id']) ? $_GET['course_id'] : (isset($_POST['course_id']) ? $_POST['course_id'] : '');

            } else {
                $errorMessage = 'Failed to send message';
            }
        }
    } catch (Exception $e) {
        $errorMessage = 'An error occurred while sending the message.';
        Logger::error('Error sending message: ' . $e->getMessage());
    }
}
?>

<?php include __DIR__ . '/../partials/header.php'; ?>
<?php include __DIR__ . '/../partials/navbar.php'; ?>


<?php if ($courseId === ''): ?>
    <div class="container">
        <h1>Send a Message</h1>
        <p>Send a message to the lecturer for this course. You will remain anonymous.</p>

        <!-- Display Success or Error Message -->
        <?php if ($messageSent): ?>
            <div class="alert alert-success">Message sent successfully!</div>
        <?php elseif (!empty($errorMessage)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <!-- Send Message Form -->
        <form action="" method="POST">
            <div class="form-group">
                <label for="course_id">Select Course</label>
                <select id="course_id" name="course_id" required>
                    <option value="">Select a course</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo htmlspecialchars($course['id'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo $courseId == $course['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($course['code'] . ' - ' . $course['name'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="message_content">Your Message</label>
                <textarea id="message_content" name="message_content" rows="4" placeholder="Type your message here..." required><?php echo htmlspecialchars($_POST['message_content'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Send Message</button>
        </form>
        <?php

         ?>

    </div>
<?php else: ?>
    <div class="container">
        <h1>Send a Message to lecturer for
            <?php foreach ($courses as $course): ?>
                <?php if ($course['id'] == $courseId): ?>
                    <?php echo htmlspecialchars($course['code'] . ' - ' . $course['name'], ENT_QUOTES, 'UTF-8'); ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </h1>
        <p>Send a message to the lecturer for this course. You will remain anonymous.</p>

        <!-- Display Success or Error Message -->
        <?php if ($messageSent): ?>
            <div class="alert alert-success">Message sent successfully!</div>
        <?php elseif (!empty($errorMessage)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <!-- Send Message Form -->
        <form action="" method="POST">
            <div class="form-group">
                <label for="message_content">Your Message</label>
                <textarea id="message_content" name="message_content" rows="4" placeholder="Type your message here..." required><?php echo htmlspecialchars($_POST['message_content'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Send Message</button>
        </form>
    </div>
<?php endif; ?>


<?php include __DIR__ . '/../partials/footer.php'; ?>