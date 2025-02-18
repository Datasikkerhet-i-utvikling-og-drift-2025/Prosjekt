<?php
session_start();
require_once __DIR__ . '/../../config/versionURL.php';
// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ' .API_BASE_URL. '/auth/login');
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

<?php include __DIR__ . '/../partials/header.php'; ?>
<?php include __DIR__ . '/../partials/navbar.php'; ?>

<div class="container">
    <h1>Manage Users</h1>
    <p>Below is the list of all users in the system. You can edit or delete users as necessary.</p>

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
                    <td><?= sanitize($user['id']) ?></td>
                    <td><?= sanitize($user['name']) ?></td>
                    <td><?= sanitize($user['email']) ?></td>
                    <td><?= sanitize($user['role']) ?></td>
                    <td>
                        <a href="<?= APP_BASE_URL ?>/admin/edit-user?user_id=<?= sanitize($user['id']) ?>" class="btn btn-edit">Edit</a>
                        <form action="" method="POST" style="display: inline;">
                            <input type="hidden" name="delete_user_id" value="<?= sanitize($user['id']) ?>">
                            <button type="submit" class="btn btn-delete">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
