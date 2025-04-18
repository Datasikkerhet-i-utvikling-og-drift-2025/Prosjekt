<?php
session_start();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'lecturer') {
    header('Location: /auth/login');
    exit;
}

// Get course information from query parameters
// $courseId = htmlspecialchars($_GET['course_id'] ?? '', ENT_QUOTES, 'UTF-8');
// if (empty($courseId)) {
//     echo 'Course ID is required.';
//     exit;
// }

// Initialize variables
$messages = [];
$errorMessage = '';


try {
    require_once __DIR__ . '/../../config/Database.php';
    require_once __DIR__ . '/../../helpers/Logger.php';

    $db = new \db\Database();
    $pdo = $db->getConnection();

    // Fetch messages for the specified course
    $stmt = $pdo->prepare("
    SELECT m.id, m.content, m.created_at, m.reply, c.code as course_code
    FROM messages m
    JOIN courses c ON m.course_id = c.id
    WHERE c.lecturer_id = :lecturer_id
    ORDER BY m.created_at DESC
");
$stmt->execute([':lecturer_id' => $_SESSION['user']['id']]);
$messages = $stmt->fetchAll();

$courseCode = $messages[0]['course_code'] ?? 'Unknown Course';
} catch (Exception $e) {
    $errorMessage = 'Failed to load messages. Please try again later.';
    Logger::error('Error fetching messages for course ID ' . $courseId . ': ' . $e->getMessage());
}

//Fetch guest comments
$stmtComments = $pdo->query("SELECT message_id, guest_name, content FROM comments");
$comments = $stmtComments->fetchAll();
?>

<?php include __DIR__ . '/../partials/header.php'; ?>
<?php include __DIR__ . '/../partials/navbar.php'; ?>

<div class="container">
<h1>Messages for Course: <?php echo htmlspecialchars($courseCode, ENT_QUOTES, 'UTF-8'); ?></h1>
    <p>Below are the messages sent by students for this course.</p>

    <!-- Error Message -->
    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
        </div>
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
                    <?php foreach ($comments as $comment): ?>
                        <?php if ($comment['message_id'] === $message['id']): ?>
                            <div class="guest-item">
                                <p><strong>Guest name: </strong> <?php echo htmlspecialchars($comment['guest_name'] != '' ? $comment['guest_name'] : 'Anonym', ENT_QUOTES, 'UTF-8'); ?></p>
                                <p> <strong>comment: </strong> <?php echo htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                        <?php else: ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <a href="/lecturer/reply?message_id=<?php echo htmlspecialchars($message['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary">Reply</a>
                    <hr>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
