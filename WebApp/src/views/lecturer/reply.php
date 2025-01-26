<?php
session_start();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'lecturer') {
    header('Location: /auth/login.php');
    exit;
}

// Get message information from query parameters
$messageId = htmlspecialchars($_GET['message_id'] ?? '', ENT_QUOTES, 'UTF-8');
if (empty($messageId)) {
    echo 'Message ID is required.';
    exit;
}

// Initialize variables
$message = null;
$errorMessage = '';
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle reply submission
    $replyContent = htmlspecialchars($_POST['reply_content'] ?? '', ENT_QUOTES, 'UTF-8');

    if (!empty($replyContent)) {
        try {
            require_once '../../helpers/Database.php';

            $db = new \db\Database();
            $pdo = $db->getConnection();

            // Update the reply in the database
            $stmt = $pdo->prepare("UPDATE messages SET reply = :reply WHERE id = :message_id");
            $stmt->execute([
                ':reply' => $replyContent,
                ':message_id' => $messageId,
            ]);

            $successMessage = 'Reply sent successfully!';
        } catch (Exception $e) {
            $errorMessage = 'Failed to send reply. Please try again later.';
        }
    } else {
        $errorMessage = 'Reply content cannot be empty.';
    }
}

try {
    require_once '../../helpers/Database.php';

    $db = new \db\Database();
    $pdo = $db->getConnection();

    // Fetch the message details
    $stmt = $pdo->prepare("SELECT content, created_at, reply FROM messages WHERE id = :message_id");
    $stmt->execute([':message_id' => $messageId]);
    $message = $stmt->fetch();
} catch (Exception $e) {
    $errorMessage = 'Failed to load the message. Please try again later.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reply to Message - Feedback System</title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Include your CSS -->
</head>
<body>
<?php include '../partials/navbar.php'; ?> <!-- Include Navbar -->

<div class="container">
    <h1>Reply to Message</h1>
    <p>Provide a reply to the student's message below.</p>

    <!-- Error or Success Messages -->
    <?php if (!empty($errorMessage)): ?>
        <div style="color: red;"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php elseif (!empty($successMessage)): ?>
        <div style="color: green;"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <!-- Message Content -->
    <?php if ($message): ?>
        <div id="message-container">
            <p><strong>Message:</strong> <?php echo htmlspecialchars($message['content'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>Sent At:</strong> <?php echo htmlspecialchars($message['created_at'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>Current Reply:</strong> <?php echo htmlspecialchars($message['reply'] ?? 'No reply yet', ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
    <?php else: ?>
        <p>Message not found.</p>
    <?php endif; ?>

    <!-- Reply Form -->
    <form id="reply-form" action="" method="POST">
        <div class="form-group">
            <label for="reply_content">Your Reply</label>
            <textarea id="reply_content" name="reply_content" rows="4" placeholder="Type your reply here..." required></textarea>
        </div>

        <button type="submit">Send Reply</button>
    </form>
</div>

<?php include '../partials/footer.php'; ?> <!-- Include Footer -->
</body>
</html>
