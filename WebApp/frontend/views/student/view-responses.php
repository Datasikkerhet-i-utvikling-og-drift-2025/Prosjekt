<?php
session_start();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header('Location: /auth/login');
    exit;
}

// Get the student's name for display
$studentName = $_SESSION['user']['name'] ?? 'Student';

// Fetch messages with responses from the database
$responses = [];
$errorMessage = '';

try {
    require_once __DIR__ . '/../../config/DatabaseManager.php';
    require_once __DIR__ . '/../../helpers/Logger.php';

    $db = new service\DatabaseService();
    $pdo = $db->pdo;

    $studentId = $_SESSION['user']['id'];

    // Fetch student messages with replies and course names
    $stmt = $pdo->prepare("
        SELECT m.id, m.content, m.reply, m.created_at, c.name AS course_name
        FROM messages m
        JOIN courses c ON m.course_id = c.id
        WHERE m.student_id = :student_id
        ORDER BY m.created_at DESC
    ");
    $stmt->execute([':student_id' => $studentId]);
    $responses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch guest comments for each message
    foreach ($responses as &$response) {
        $stmtComments = $pdo->prepare("SELECT guest_name, content FROM comments WHERE message_id = :message_id");
        $stmtComments->execute([':message_id' => $response['id']]);
        $response['comments'] = $stmtComments->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $errorMessage = 'Failed to load messages and responses. Please try again later.';
    Logger::error('Error fetching student responses: ' . $e->getMessage());
}
?>

<?php include __DIR__ . '/../partials/header.php'; ?>
<?php include __DIR__ . '/../partials/navbar.php'; ?>

<div class="container">
    <h1>Responses to Your Messages</h1>
    <p>Here you can view all your messages and any responses from the lecturers.</p>

    <!-- Error Message -->
    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <!-- Messages and Responses Section -->
    <div id="responses-container">
        <?php if (empty($responses)): ?>
            <p>You have not sent any messages yet.</p>
        <?php else: ?>
            <?php foreach ($responses as $response): ?>
                <div class="message-item">
                <p><strong>Sent At:</strong> <?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($response['created_at'])), ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><strong>Course:</strong> <?php echo htmlspecialchars($response['course_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><strong>your message:</strong> <?php echo htmlspecialchars($response['content'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><strong>Lecturer's Response:</strong> <?php echo htmlspecialchars($response['reply'] ?? 'No response yet', ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php if (!empty($response['comments'])): ?>
                        <?php foreach ($response['comments'] as $comment): ?>
                            <div class="guest-item">
                                <p><strong>Guest name:</strong> <?php echo htmlspecialchars($comment['guest_name'] != '' ? $comment['guest_name'] : 'Anonym', ENT_QUOTES, 'UTF-8'); ?></p>
                                <p><strong>Comment:</strong> <?php echo htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No comments yet.</p>
                    <?php endif; ?>
                    <hr>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>