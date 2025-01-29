<?php
session_start();

// Dynamically construct the API base URL using the server's host
$usersApiUrl = "http://localhost/src/api/index.php?route=users"; // API endpoint to fetch users
$loginApiUrl = "http://backend:80/src/api/index.php?route=users"; // Backend service name in Docker network

$error = null;
$users = [];

// Fetch the users list
$response = @file_get_contents($usersApiUrl);
if ($response !== false) {
    $users = json_decode($response, true)['data'] ?? [];
} else {
    $error = "Error connecting to the backend. Please check the API configuration. " . error_get_last()['message'];
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    // Send user input to the backend
    $postData = http_build_query([
        'email' => $_POST['email'],
        'password' => $_POST['password'],
    ]);

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => $postData,
        ],
    ];

    if (!empty($loginApiUrl)) {
        $context  = stream_context_create($options);
        $result = @file_get_contents($loginApiUrl, false, $context);
        $response = json_decode($result, true);

            if (isset($response['message']) && $response['message'] === 'Login successful') {
                // Set user session
                $_SESSION['user'] = $response['user'];
                // Redirect to user page
                header("Location: user.php");
                exit;
            } else {
                $error = isset($response['error']) ? $response['error'] : 'Login failed';
            }
        } else {
            $error = "Error connecting to the backend. Please check the API configuration. " . error_get_last()['message'];
        }
    } else {
        $error = 'Login API URL is not configured.';
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>
    <link rel="stylesheet" href="style.css"> <!-- Link the CSS file -->
</head>
<body>
<section class="navigation">
    <a href="registerStudent.php">Register</a>
</section>
<div class="container">
    <h1>Users</h1>

    <!-- Display error message if any -->
    <?php if (!empty($error)): ?>
        <div class="error" style="color: red;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <h2>Login</h2>
    <form action="index.php" method="POST">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br>
        <button type="submit" name="login">Login</button>
    </form>

    <h2>Users List</h2>
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Created At</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($users)): ?>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['user_id']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">No users found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>