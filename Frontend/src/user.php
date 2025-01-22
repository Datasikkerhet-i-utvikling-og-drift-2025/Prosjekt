<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: /loggInn.php");
    exit;
}

$user = $_SESSION['user'];
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
    </style>
</head>
<body>
    <div class="user-info">
        <h2>Welcome, <?= htmlspecialchars($user['email']) ?></h2>
        <p><strong>User Type:</strong> <?= htmlspecialchars($user['user_type']) ?></p>
        <p><strong>Created At:</strong> <?= htmlspecialchars($user['created_at']) ?></p>
        <a href="logout.php">Logout</a>
    </div>
</body>
</html>