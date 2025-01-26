<?php
session_start();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /auth/login.php');
    exit;
}

// Include database connection
require_once '../src/config/Database.php';

// Function to sanitize output
function sanitize($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Initialize variables
$successMessage = '';
$errorMessage = '';

// Fetch reported messages from the database
try {
    $pdo = (new \db\Database())->getConnection();
    $stmt = $pdo->query("SELECT r.id AS report_id, m.id AS message_id, m.content AS message_content, 
                                r.reported_by, r.reason 
                         FROM reports r 
                         JOIN messages m ON r.message_id = m.id");
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reports - Feedback System</title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Include your CSS -->
</head>
<body>
<?php include '../src/views/partials/navbar.php'; ?> <!-- Include Navbar -->

<div class="container">
    <h1>Manage Reports</h1>
    <p>Below is the list of reported messages. Review the reports and take appropriate actions.</p>

    <!-- Success/Error Messages -->
    <?php if ($successMessage): ?>
        <div class="success-message" style="color: green; margin-bottom: 20px;">
            <?php echo sanitize($successMessage); ?>
        </div>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <div class="error-message" style="color: red; margin-bottom: 20px;">
            <?php echo sanitize($errorMessage); ?>
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
                    <td><?php echo sanitize($report['report_id']); ?></td>
                    <td><?php echo sanitize($report['message_content']); ?></td>
                    <td><?php echo sanitize($report['reported_by'] ?: 'Anonymous'); ?></td>
                    <td><?php echo sanitize($report['reason']); ?></td>
                    <td>
                        <form action="" method="POST" style="display: inline;">
                            <input type="hidden" name="dismiss_report_id" value="<?php echo sanitize($report['report_id']); ?>">
                            <button type="submit" class="btn btn-dismiss">Dismiss</button>
                        </form>
                        <form action="" method="POST" style="display: inline;">
                            <input type="hidden" name="delete_message_id" value="<?php echo sanitize($report['message_id']); ?>">
                            <button type="submit" class="btn btn-delete">Delete Message</button>
                        </form>
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

    .btn-dismiss {
        background-color: #28a745;
        border: none;
        padding: 5px 10px;
        color: white;
        border-radius: 5px;
        cursor: pointer;
    }

    .btn-dismiss:hover {
        background-color: #218838;
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
</style>
</body>
</html>
