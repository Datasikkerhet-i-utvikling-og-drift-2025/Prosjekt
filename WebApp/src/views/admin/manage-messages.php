<?php
session_start();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /auth/login.php');
    exit;
}

// Include database connection
require_once '../src/config/database.php';

// Function to sanitize output
function sanitize($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Fetch messages from the database
try {
    $pdo = (new \db\Database())->getConnection();
    $stmt = $pdo->query("SELECT messages.id, courses.name AS course_name, messages.content, messages.reply 
                         FROM messages 
                         LEFT JOIN courses ON messages.course_id = courses.id");
    $messages = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Failed to fetch messages: " . sanitize($e->getMessage());
    $messages = [];
}

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_message_id'])) {
    $messageId = intval($_POST['delete_message_id']);

    try {
        $stmt = $pdo->prepare("DELETE FROM messages WHERE id = :id");
        $stmt->execute(['id' => $messageId]);
        header("Location: /admin/manage-messages.php?success=Message deleted successfully.");
        exit;
    } catch (PDOException $e) {
        $error = "Failed to delete message: " . sanitize($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Messages - Feedback System</title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Include your CSS -->
</head>
<body>
<?php include '../src/views/partials/navbar.php'; ?> <!-- Include Navbar -->

<div class="container">
    <h1>Manage Messages</h1>
    <p>Below is the list of all messages in the system. You can delete messages or view details for further actions.</p>

    <!-- Success/Error Message -->
    <?php if (isset($_GET['success'])): ?>
        <div class="success-message" style="color: green;">
            <?php echo sanitize($_GET['success']); ?>
        </div>
    <?php elseif (isset($error)): ?>
        <div class="error-message" style="color: red;">
            <?php echo sanitize($error); ?>
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
                    <td><?php echo sanitize($message['id']); ?></td>
                    <td><?php echo sanitize($message['course_name']); ?></td>
                    <td><?php echo sanitize($message['content']); ?></td>
                    <td><?php echo sanitize($message['reply'] ?? 'No reply yet'); ?></td>
                    <td>
                        <form action="" method="POST" style="display:inline;">
                            <input type="hidden" name="delete_message_id" value="<?php echo sanitize($message['id']); ?>">
                            <button type="submit" class="btn btn-delete">Delete</button>
                        </form>
                        <a href="/admin/view-message.php?message_id=<?php echo sanitize($message['id']); ?>" class="btn btn-view">View</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
    .container {
        max-width: 1000px;
        margin: 50px auto;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: #f9f9f9;
    }

    h1 {
        text-align: center;
        margin-bottom: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    th, td {
        padding: 10px;
        text-align: left;
        border: 1px solid #ddd;
    }

    th {
        background-color: #f4f4f4;
    }

    .btn {
        display: inline-block;
        padding: 5px 10px;
        background-color: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-size: 14px;
    }

    .btn:hover {
        background-color: #0056b3;
    }

    .btn-delete {
        background-color: #dc3545;
        border: none;
        padding: 5px 10px;
        color: white;
        border-radius: 5px;
        cursor: pointer;
    }

    .btn-delete:hover {
        background-color: #a71d2a;
    }

    .btn-view {
        background-color: #17a2b8;
    }

    .btn-view:hover {
        background-color: #117a8b;
    }

    .success-message, .error-message {
        margin-bottom: 20px;
        padding: 10px;
        border-radius: 5px;
    }
</style>
</body>
</html>
