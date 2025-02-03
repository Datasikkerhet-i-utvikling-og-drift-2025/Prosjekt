<?php
session_start();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header('Location: /auth/login');
    exit;
}

// Get course information from query parameters
$courseId = $_GET['course_id'] ?? '';
if (empty($courseId)) {
    header('Location: /student/dashboard?error=Course%20ID%20is%20required');
    exit;
}

// Initialize variables
$messageSent = false;
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../../config/Database.php';
    require_once __DIR__ . '/../../helpers/Logger.php';
    require_once __DIR__ . '/../../models/Message.php';
    
    try {
        $db = new \db\Database();
        $pdo = $db->getConnection();

        $messageContent = trim($_POST['message_content'] ?? '');
        $studentId = $_SESSION['user']['id'];
        
        // Generer anonymous_id (UUID)
        $anonymousId = bin2hex(random_bytes(16));

        if (empty($messageContent)) {
            $errorMessage = 'Message content cannot be empty.';
        } else {
            $messageModel = new Message($pdo);
            if ($messageModel->createMessage($studentId, $courseId, $anonymousId, $messageContent)) {
                $messageSent = true;
                Logger::info("Message sent successfully with anonymous ID");
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
        <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($courseId, ENT_QUOTES, 'UTF-8'); ?>" />

        <div class="form-group">
            <label for="message_content">Your Message</label>
            <textarea id="message_content" name="message_content" rows="4" placeholder="Type your message here..." required><?php echo htmlspecialchars($_POST['message_content'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Send Message</button>
    </form>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
