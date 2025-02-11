<?php
session_start();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /auth/login');
    exit;
}

// Include database connection
require_once __DIR__ . '/../../config/Database.php';

// Function to sanitize output
function sanitize($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Initialize variables
$successMessage = '';
$errorMessage = '';

// Fetch reported messages from the database
try {
    $pdo = (new helpers\Database())->pdo;
    $stmt = $pdo->query("
        SELECT r.id AS report_id, m.id AS message_id, m.content AS message_content, 
               r.reported_by, r.reason 
        FROM reports r 
        JOIN messages m ON r.message_id = m.id
    ");
    $reports = $stmt->fetchAll();
} catch (PDOException $e) {
    $errorMessage = "Failed to fetch reports: " . sanitize($e->getMessage());
    $reports = [];
}

// Handle dismiss report request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dismiss_report_id'])) {
    $reportId = intval($_POST['dismiss_report_id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM reports WHERE id = :id");
        $stmt->execute(['id' => $reportId]);
        $successMessage = "Report dismissed successfully.";
    } catch (PDOException $e) {
        $errorMessage = "Failed to dismiss report: " . sanitize($e->getMessage());
    }
}

// Handle delete message request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_message_id'])) {
    $messageId = intval($_POST['delete_message_id']);
    try {
        // Delete the message and associated reports
        $stmt = $pdo->prepare("DELETE FROM messages WHERE id = :id");
        $stmt->execute(['id' => $messageId]);
        $successMessage = "Message deleted successfully.";
    } catch (PDOException $e) {
        $errorMessage = "Failed to delete message: " . sanitize($e->getMessage());
    }
}
?>

<?php include __DIR__ . '/../partials/header.php'; ?>
<?php include __DIR__ . '/../partials/navbar.php'; ?>

<div class="container">
    <h1>Manage Reports</h1>
    <p>Below is the list of reported messages. Review the reports and take appropriate actions.</p>

    <!-- Success/Error Messages -->
    <?php if ($successMessage): ?>
        <div class="alert alert-success">
            <?= sanitize($successMessage) ?>
        </div>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <div class="alert alert-error">
            <?= sanitize($errorMessage) ?>
        </div>
    <?php endif; ?>

    <!-- Reports Table -->
    <table class="table">
        <thead>
        <tr>
            <th>Report ID</th>
            <th>Message Content</th>
            <th>Reported By</th>
            <th>Reason</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($reports)): ?>
            <tr>
                <td colspan="5">No reports found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($reports as $report): ?>
                <tr>
                    <td><?= sanitize($report['report_id']) ?></td>
                    <td><?= sanitize($report['message_content']) ?></td>
                    <td><?= sanitize($report['reported_by'] ?: 'Anonymous') ?></td>
                    <td><?= sanitize($report['reason']) ?></td>
                    <td>
                        <form action="" method="POST" style="display: inline;">
                            <input type="hidden" name="dismiss_report_id" value="<?= sanitize($report['report_id']) ?>">
                            <button type="submit" class="btn btn-dismiss">Dismiss</button>
                        </form>
                        <form action="" method="POST" style="display: inline;">
                            <input type="hidden" name="delete_message_id" value="<?= sanitize($report['message_id']) ?>">
                            <button type="submit" class="btn btn-delete">Delete Message</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
