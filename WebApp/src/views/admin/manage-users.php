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

// Fetch users from the database
try {
    $pdo = (new \db\Database())->getConnection();
    $stmt = $pdo->query("SELECT id, name, email, role FROM users");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $errorMessage = "Failed to fetch users: " . sanitize($e->getMessage());
    $users = [];
}

// Handle delete user request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $userId = intval($_POST['delete_user_id']);

    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $successMessage = "User deleted successfully.";
        // Refresh the list of users
        $stmt = $pdo->query("SELECT id, name, email, role FROM users");
        $users = $stmt->fetchAll();
    } catch (PDOException $e) {
        $errorMessage = "Failed to delete user: " . sanitize($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Feedback System</title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Include your CSS -->
</head>
<body>
<?php include '../src/views/partials/navbar.php'; ?> <!-- Include Navbar -->

<div class="container">
    <h1>Manage Users</h1>
    <p>Below is the list of all users in the system. You can edit or delete users as necessary.</p>

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

    <!-- Users Table -->
    <table class="table">
        <thead>
        <tr>
            <th>User ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($users)): ?>
            <tr>
                <td colspan="5">No users found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo sanitize($user['id']); ?></td>
                    <td><?php echo sanitize($user['name']); ?></td>
                    <td><?php echo sanitize($user['email']); ?></td>
                    <td><?php echo sanitize($user['role']); ?></td>
                    <td>
                        <a href="/admin/edit-user.php?user_id=<?php echo sanitize($user['id']); ?>" class="btn btn-edit">Edit</a>
                        <form action="" method="POST" style="display: inline;">
                            <input type="hidden" name="delete_user_id" value="<?php echo sanitize($user['id']); ?>">
                            <button type="submit" class="btn btn-delete">Delete</button>
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

    .btn-edit {
        background-color: #ffc107;
    }

    .btn-edit:hover {
        background-color: #e0a800;
    }
</style>
</body>
</html>
