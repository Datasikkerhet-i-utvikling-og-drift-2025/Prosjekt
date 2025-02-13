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
    require_once __DIR__ . '/../../config/DatabaseService.php';
    require_once __DIR__ . '/../../helpers/Logger.php';

    $db = new service\DatabaseService();
    $pdo = $db->pdo;

    $studentId = $_SESSION['user']['id'];

    // Fetch student messages with replies
    $stmt = $pdo->prepare("
        SELECT content, reply, created_at 
        FROM messages 
        WHERE student_id = :student_id 
        ORDER BY created_at DESC
    ");
    $stmt->execute([':student_id' => $studentId]);
    $responses = $stmt->fetchAll();
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
                    <p><strong>Message:</strong> <?php echo htmlspecialchars($response['content'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><strong>Response:</strong> <?php echo htmlspecialchars($response['reply'] ?? 'No response yet', ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><strong>Sent At:</strong> <?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($response['created_at'])), ENT_QUOTES, 'UTF-8'); ?></p>
                    <hr>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
