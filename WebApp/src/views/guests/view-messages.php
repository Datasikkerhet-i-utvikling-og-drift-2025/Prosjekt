<?php
session_start();

// Include required files using __DIR__
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../../config/Database.php';

// Sanitize input
function sanitize($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Retrieve course details from query parameters
$courseCode = sanitize($_GET['course_code'] ?? '');
$pinCode = sanitize($_GET['pin_code'] ?? '');

// Validate course code and PIN code
if (empty($courseCode) || empty($pinCode)) {
    echo '<div class="alert alert-danger">Invalid course code or PIN code provided.</div>';
    include __DIR__ . '/../partials/footer.php';
    exit;
}

// Initialize variables
$messages = [];
$errorMessage = '';

try {
    // Fetch messages from the database
    $db = new \db\Database();
    $pdo = $db->getConnection();

    $stmt = $pdo->prepare("
        SELECT m.id, m.content, m.reply, m.created_at 
        FROM messages m
        JOIN courses c ON m.course_id = c.id
        WHERE c.code = :course_code AND c.pin_code = :pin_code
        ORDER BY m.created_at DESC
    ");
    $stmt->execute([
        ':course_code' => $courseCode,
        ':pin_code' => $pinCode,
    ]);
    $messages = $stmt->fetchAll();
} catch (Exception $e) {
    $errorMessage = 'Error loading messages. Please try again later.';
}
?>

<div class="container">
    <h1>Messages for Course: <?php echo sanitize($courseCode); ?></h1>

    <!-- Error Message -->
    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger"><?php echo sanitize($errorMessage); ?></div>
    <?php endif; ?>

    <!-- Messages List -->
    <div id="messages-container">
        <?php if (empty($messages)): ?>
            <p>No messages available for this course.</p>
        <?php else: ?>
            <?php foreach ($messages as $message): ?>
                <div class="message-item">
                    <p><strong>Message:</strong> <?php echo sanitize($message['content']); ?></p>
                    <p><strong>Reply:</strong> <?php echo sanitize($message['reply'] ?? 'No reply yet'); ?></p>
                    <p><strong>Sent At:</strong> <?php echo sanitize($message['created_at']); ?></p>
                    <div class="message-actions">
                        <a href="/guest/report-message.php?message_id=<?php echo sanitize($message['id']); ?>" class="btn btn-secondary">Report</a>
                        <a href="/guest/comment.php?message_id=<?php echo sanitize($message['id']); ?>" class="btn btn-primary">Comment</a>
                    </div>
                    <hr>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Back to Courses Link -->
    <p>
        <a href="/guest/view-courses" class="btn btn-secondary">Back to Courses</a>
    </p>
</div>

<?php
// Include the footer
require_once __DIR__ . '/../partials/footer.php';
?>
