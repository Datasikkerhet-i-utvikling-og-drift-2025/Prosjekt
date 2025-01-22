<?php
// Dynamically construct the API base URL using the server's host
$apiBaseUrl = "http://backend:80/api/index.php?route=users"; // Assumes 'backend' is resolvable in the Docker network

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Safely retrieve POST data and validate it
    $firstName = $_POST['first_name'] ?? null;
    $lastName = $_POST['last_name'] ?? null;
    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;
    $userType = $_POST['user_type'] ?? null;

    // Validate input fields
    if (!$firstName || !$lastName || !$email || !$password || !$userType) {
        $error = "All fields are required.";
    } else {
        // Prepare data for POST request
        $postData = json_encode([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => $password,
            'user_type' => $userType,
        ]);

        // Create HTTP context for the POST request
        $options = [
            'http' => [
                'header'  => "Content-Type: application/json\r\n",
                'method'  => 'POST',
                'content' => $postData,
            ],
        ];
        $context = stream_context_create($options);

        // Send the POST request
        $result = @file_get_contents($apiBaseUrl, false, $context);

        if ($result === false) {
            $error = "Error connecting to the backend. Please check the API configuration.";
        } else {
            $response = json_decode($result, true);
            if (!$response['success']) {
                $error = $response['message'] ?? "Failed to add the user.";
            }
        }
    }
}

// Fetch the users list
$response = @file_get_contents($apiBaseUrl);
if ($response === false) {
    $error = $error ?? "Failed to fetch users. The backend might be unreachable.";
    $users = [];
} else {
    $users = json_decode($response, true)['data'] ?? [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frontend Example</title>
    <link rel="stylesheet" href="style.css"> <!-- Link the CSS file -->
</head>
<body>
<section class="navigation">
    <a href="loggInn.php">Go to Login</a>
</section>
<div class="container">
    <h1>Manage Users</h1>

    <!-- Display error message if any -->
    <?php if (!empty($error)): ?>
        <div class="error" style="color: red;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <h2>Add New User</h2>
    <form action="" method="POST">
        <label for="first_name">First Name:</label>
        <input type="text" id="first_name" name="first_name" required>

        <label for="last_name">Last Name:</label>
        <input type="text" id="last_name" name="last_name" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <label for="user_type">User Type:</label>
        <select id="user_type" name="user_type" required>
            <option value="student">Student</option>
            <option value="lecturer">Lecturer</option>
            <option value="admin">Admin</option>
        </select>

        <button type="submit">Add User</button>
    </form>

    <h2>Users</h2>
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Email</th>
            <th>User Type</th>
            <th>Created At</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($users)): ?>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['user_id']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['user_type']) ?></td>
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
