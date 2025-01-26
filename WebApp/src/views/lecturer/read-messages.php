<?php
session_start();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'lecturer') {
    header('Location: /auth/login.php');
    exit;
}

// Get course information from query parameters
$courseId = htmlspecialchars($_GET['course_id'] ?? '', ENT_QUOTES, 'UTF-8');
if (empty($courseId)) {
    echo 'Course ID is required.';
    exit;
}

// Initialize variables
$messages = [];
$errorMessage = '';

try {
    require_once '../../helpers/Database.php';

    $db = new \db\Database();
    $pdo = $db->getConnection();

    // Fetch messages for the specified course
    $stmt = $pdo->prepare("
        SELECT m.id, m.content, m.created_at, m.reply
        FROM messages m
        WHERE m.course_id = :course_id
        ORDER BY m.created_at DESC
    ");
    $stmt->execute([':course_id' => $courseId]);
    $messages = $stmt->fetchAll();
} catch (Exception $e) {
    $errorMessage = 'Failed to load messages. Please try again later.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Read Messages - Feedback System</title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Include your CSS -->
</head>
<body>
<?php include '../partials/navbar.php'; ?> <!-- Include Navbar -->

<div class="container">
    <h1>Messages for Course ID: <?php echo $courseId; ?></h1>
    <p>Below are the messages sent by students for this course.</p>

    <!-- Error Message Placeholder -->
    <?php if (!empty($errorMessage)): ?>
        <div style="color: red;"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <!-- Messages Section -->
    <?php if (empty($messages)): ?>
        <p>No messages found for this course.</p>
    <?php else: ?>
        <div id="messages-container">
            <?php foreach ($messages as $message): ?>
                <div class="message-item">
                    <p><strong>Message:</strong> <?php echo htmlspecialchars($message['content'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><strong>Sent At:</strong> <?php echo htmlspecialchars($message['created_at'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><strong>Response:</strong> <?php echo htmlspecialchars($message['reply'] ?? 'No response yet', ENT_QUOTES, 'UTF-8'); ?></p>
                    <a href="/lecturer/reply.php?message_id=<?php echo htmlspecialchars($message['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn">Reply</a>
                    <hr>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../partials/footer.php'; ?> <!-- Include Footer -->
</body>
</html>
