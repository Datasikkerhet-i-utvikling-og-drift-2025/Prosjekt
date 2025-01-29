<?php
session_start();

// Include required files using __DIR__
require_once __DIR__ . '/../../helpers/Database.php';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';

// Sanitize output
function sanitize($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Check if a message ID is provided
$messageId = $_GET['message_id'] ?? null;
if (!$messageId) {
    echo "<div class='alert alert-danger'>Message ID is required to report a message.</div>";
    include __DIR__ . '/../partials/footer.php';
    exit;
}

// Initialize variables
$errorMessage = '';
$successMessage = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportReason = $_POST['report_reason'] ?? '';
    $guestName = $_POST['guest_name'] ?? 'Anonymous';

    if (empty($reportReason)) {
        $errorMessage = 'Reason for reporting is required.';
    } else {
        try {
            $pdo = (new \db\Database())->getConnection();

            // Insert the report into the database
            $stmt = $pdo->prepare("
                INSERT INTO reports (message_id, reported_by, reason, created_at) 
                VALUES (:message_id, :reported_by, :reason, NOW())
            ");
            $stmt->execute([
                ':message_id' => $messageId,
                ':reported_by' => $guestName,
                ':reason' => sanitize($reportReason),
            ]);

            $successMessage = 'Message reported successfully. Thank you for your feedback.';
        } catch (PDOException $e) {
            $errorMessage = 'Failed to report the message. Please try again later.';
        }
    }
}

// Fetch the message details to display
$messageContent = '';
try {
    $pdo = (new \db\Database())->getConnection();

    $stmt = $pdo->prepare("SELECT content FROM messages WHERE id = :id");
    $stmt->execute([':id' => $messageId]);
    $message = $stmt->fetch();

    if ($message) {
        $messageContent = $message['content'];
    } else {
        echo "<div class='alert alert-danger'>Message not found.</div>";
        include __DIR__ . '/../partials/footer.php';
        exit;
    }
} catch (PDOException $e) {
    $errorMessage = 'Failed to fetch message details. Please try again later.';
}
?>

<div class="container">
    <h1>Report a Message</h1>

    <!-- Success or Error Message -->
    <?php if ($successMessage): ?>
        <div class="alert alert-success">
            <?= sanitize($successMessage) ?>
        </div>
    <?php elseif ($errorMessage): ?>
        <div class="alert alert-danger">
            <?= sanitize($errorMessage) ?>
        </div>
    <?php endif; ?>

    <!-- Message Details -->
    <div class="message-details">
        <p><strong>Message Content:</strong> <?= sanitize($messageContent) ?></p>
    </div>

    <!-- Report Message Form -->
    <form action="" method="POST">
        <div class="form-group">
            <label for="guest_name">Your Name (Optional)</label>
            <input type="text" id="guest_name" name="guest_name" placeholder="Enter your name (optional)">
        </div>

        <div class="form-group">
            <label for="report_reason">Reason for Reporting</label>
            <textarea id="report_reason" name="report_reason" rows="4" placeholder="Explain why this message should be reported..." required></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Submit Report</button>
    </form>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
