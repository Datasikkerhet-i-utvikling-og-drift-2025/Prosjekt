<?php
$apiUrl = "http://backend:80/"; // Backend service name in Docker network

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Send user input to the backend
    $postData = http_build_query([
        'email' => $_POST['email'],
        'password' => $_POST['password'],
        'user_type' => $_POST['user_type'],
    ]);

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => $postData,
        ],
    ];
    $context  = stream_context_create($options);
    $result = file_get_contents($apiUrl, false, $context);
}

// Fetch updated users
$response = file_get_contents($apiUrl);
$users = json_decode($response, true);
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
    <div class="container">
        <h1>Manage Users</h1>

        <h2>Add New User</h2>
        <form action="" method="POST">
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
                            <td><?= $user['user_id'] ?></td>
                            <td><?= $user['email'] ?></td>
                            <td><?= $user['user_type'] ?></td>
                            <td><?= $user['created_at'] ?></td>
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
