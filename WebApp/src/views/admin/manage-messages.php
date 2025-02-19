<?php
session_start();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /auth/login');
    exit;
}

// Include required files using __DIR__
require_once __DIR__ . '/../../helpers/Database.php';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';

// Sanitize output
function sanitize($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Initialize variables
$messages = [];
$errorMessage = '';
$successMessage = $_GET['success'] ?? '';

// Fetch messages from the database
try {
    $pdo = (new \db\Database())->getConnection();
    $stmt = $pdo->query("
        SELECT messages.id, courses.name AS course_name, messages.content, messages.reply 
        FROM messages 
        LEFT JOIN courses ON messages.course_id = courses.id
        ORDER BY messages.created_at DESC
    ");
    $messages = $stmt->fetchAll();
} catch (PDOException $e) {
    $errorMessage = "Failed to fetch messages: " . sanitize($e->getMessage());
}

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_message_id'])) {
    $messageId = intval($_POST['delete_message_id']);

    try {
        $stmt = $pdo->prepare("DELETE FROM messages WHERE id = :id");
        $stmt->execute(['id' => $messageId]);
        header("Location: /admin/manage-messages?success=Message deleted successfully.");
        exit;
    } catch (PDOException $e) {
        $errorMessage = "Failed to delete message: " . sanitize($e->getMessage());
    }
}
?>

<div class="container">
    <h1>Manage Messages</h1>
    <p>Below is the list of all messages in the system. You can delete messages or view details for further actions.</p>

    <!-- Success/Error Message -->
    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success">
            <?= sanitize($successMessage) ?>
        </div>
    <?php elseif (!empty($errorMessage)): ?>
        <div class="alert alert-danger">
            <?= sanitize($errorMessage) ?>
        </div>
    <?php endif; ?>

    <!-- Messages Table -->
    <table class="table">
        <thead>
        <tr>
            <th>Message ID</th>
            <th>Course</th>
            <th>Content</th>
            <th>Reply</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($messages)): ?>
            <tr>
                <td colspan="5">No messages found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($messages as $message): ?>
                <tr>
                    <td><?= sanitize($message['id']) ?></td>
                    <td><?= sanitize($message['course_name']) ?></td>
                    <td><?= sanitize($message['content']) ?></td>
                    <td><?= sanitize($message['reply'] ?? 'No reply yet') ?></td>
                    <td>
                        <form action="" method="POST" style="display:inline;">
                            <input type="hidden" name="delete_message_id" value="<?= sanitize($message['id']) ?>">
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                        <a href="/admin/view-message?message_id=<?= sanitize($message['id']) ?>" class="btn btn-info">View</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
