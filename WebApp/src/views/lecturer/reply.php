<?php
session_start();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'lecturer') {
    header('Location: /auth/login');
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

try {
    require_once __DIR__ . '/../../config/DatabaseManager.php';
    require_once __DIR__ . '/../../helpers/Logger.php';

    $db = new service\DatabaseService();
    $pdo = $db->pdo;

    // Handle reply submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $replyContent = htmlspecialchars($_POST['reply_content'] ?? '', ENT_QUOTES, 'UTF-8');

        if (!empty($replyContent)) {
            try {
                // Update the reply in the database
                $stmt = $pdo->prepare("UPDATE messages SET reply = :reply, updated_at = NOW() WHERE id = :message_id");
                $stmt->execute([
                    ':reply' => $replyContent,
                    ':message_id' => $messageId,
                ]);

                $successMessage = 'Reply sent successfully!';
                Logger::info("Reply sent for message ID $messageId by lecturer ID {$_SESSION['user']['id']}.");
            } catch (Exception $e) {
                $errorMessage = 'Failed to send reply. Please try again later.';
                Logger::error("Error sending reply for message ID $messageId: " . $e->getMessage());
            }
        } else {
            $errorMessage = 'Reply content cannot be empty.';
        }
    }

    // Fetch the message details
    $stmt = $pdo->prepare("SELECT content, created_at, reply, id FROM messages WHERE id = :message_id");
    $stmt->execute([':message_id' => $messageId]);
    $message = $stmt->fetch();
    //Fetch guest comments
    $stmtComments = $pdo->query("SELECT message_id, guest_name, content FROM comments");
    $comments = $stmtComments->fetchAll();
} catch (Exception $e) {
    $errorMessage = 'Failed to load the message. Please try again later.';
    Logger::error("Error fetching message ID $messageId: " . $e->getMessage());
}
?>

<?php include __DIR__ . '/../partials/header.php'; ?>
<?php include __DIR__ . '/../partials/navbar.php'; ?>

<div class="container">
    <h1>Reply to Message</h1>
    <p>Provide a reply to the student's message below.</p>

    <!-- Error or Success Messages -->
    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php elseif (!empty($successMessage)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <!-- Message Content -->
    <?php if ($message): ?>
        <div id="message-container">
            <p><strong>Message:</strong> <?php echo htmlspecialchars($message['content'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>Sent At:</strong> <?php echo htmlspecialchars($message['created_at'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php foreach ($comments as $comment): ?>
                <?php if ($comment['message_id'] === $message['id']): ?>
                    <div class="guest-item">
                        <p><strong>Guest name: </strong> <?php echo htmlspecialchars($comment['guest_name'] != '' ? $comment['guest_name'] : 'Anonym', ENT_QUOTES, 'UTF-8'); ?></p>
                        <p> <strong>comment: </strong> <?php echo htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                <?php else: ?>
                <?php endif; ?>
            <?php endforeach; ?>
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

        <button type="submit" class="btn btn-primary">Send Reply</button>
    </form>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
