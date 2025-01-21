<?php
session_start();

$apiUrl = "http://backend:80/loggInn.php"; // Backend service name in Docker network

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    $context  = stream_context_create($options);
    $result = @file_get_contents($apiUrl, false, $context);
    $response = json_decode($result, true);

    if (isset($response['message']) && $response['message'] === 'Login successful') {
        // Set user session
        $_SESSION['user'] = $response['user'];
        // Redirect to user page
        header("Location: /user.php");
        exit;
    } else {
        $error = isset($response['error']) ? $response['error'] : 'Login failed';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h1>Login</h1>

    <?php if (isset($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form action="loggInn.php" method="POST">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Logg inn</button>
    </form>
</body>
</html>