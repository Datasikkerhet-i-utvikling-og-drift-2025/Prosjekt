<?php
session_start();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header('Location: /auth/login.php');
    exit;
}

// Get the student's name for display
$studentName = $_SESSION['user']['name'] ?? 'Student';

// Fetch messages with responses from the database
$responses = [];
$errorMessage = '';

try {
    require_once '../../helpers/Database.php';

    $db = new \db\Database();
    $pdo = $db->getConnection();

    $studentId = $_SESSION['user']['id'];

    $stmt = $pdo->prepare("
        SELECT messages.content, messages.reply, messages.created_at 
        FROM messages 
        WHERE messages.student_id = :student_id 
        ORDER BY messages.created_at DESC
    ");
    $stmt->execute([':student_id' => $studentId]);
    $responses = $stmt->fetchAll();
} catch (Exception $e) {
    $errorMessage = 'Failed to load messages and responses. Please try again later.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Responses - Feedback System</title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Include your CSS -->
</head>
<body>
<?php include '../partials/navbar.php'; ?> <!-- Include Navbar -->

<div class="container">
    <h1>Responses to Your Messages</h1>
    <p>Here you can view all your messages and any responses from the lecturers.</p>

    <!-- Error Message Placeholder -->
    <?php if (!empty($errorMessage)): ?>
        <div style="color: red;"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
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
                    <p><strong>Sent At:</strong> <?php echo htmlspecialchars($response['created_at'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <hr>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include '../partials/footer.php'; ?>
</body>
</html>
