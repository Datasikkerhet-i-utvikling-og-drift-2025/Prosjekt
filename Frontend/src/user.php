<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['user'];

// Handle logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    // Clear the session
    session_unset();
    session_destroy();
    // Redirect to index.php
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .user-info {
            max-width: 400px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background: #f9f9f9;
        }
        .user-info h2 {
            margin-top: 0;
        }
        .logout-form {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="user-info">
    <h2>Welcome, <?= htmlspecialchars($user['email']) ?></h2>
    <p>User Type: <?= htmlspecialchars($user['user_type']) ?></p>
    <p>Created At: <?= htmlspecialchars($user['created_at']) ?></p>
    <form class="logout-form" action="" method="POST">
        <button type="submit" name="logout">Logout</button>
    </form>
</div>
</body>
</html>