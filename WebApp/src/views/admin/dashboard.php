<?php
session_start();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /auth/login.php');
    exit;
}

// Get the admin's name for display
$adminName = $_SESSION['user']['name'] ?? 'Administrator';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Feedback System</title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Include your CSS -->
</head>
<body>
<?php include '../src/views/partials/navbar.php'; ?> <!-- Include Navbar -->

<div class="container">
    <h1>Welcome, <?php echo htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8'); ?>!</h1>
    <p>This is your dashboard. Here you can manage users, messages, and reports.</p>

    <!-- Error Message Placeholder -->
    <div id="error-message" style="color: red; display: none;"></div>

    <!-- Manage Users Section -->
    <section>
        <h2>Manage Users</h2>
        <a href="/admin/manage-users.php" class="btn">View and Manage Users</a>
    </section>

    <!-- Manage Messages Section -->
    <section>
        <h2>Manage Messages</h2>
        <a href="/admin/manage-messages.php" class="btn">View and Manage Messages</a>
    </section>

    <!-- View Reports Section -->
    <section>
        <h2>View Reports</h2>
        <a href="/admin/reports.php" class="btn">View Reported Messages</a>
    </section>
</div>

<style>
    .container {
        max-width: 800px;
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

    section {
        margin-bottom: 30px;
    }

    .btn {
        display: inline-block;
        padding: 10px 15px;
        background-color: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-size: 16px;
    }

    .btn:hover {
        background-color: #0056b3;
    }
</style>
</body>
</html>
