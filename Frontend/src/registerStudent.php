<?php
// Dynamically construct the API base URL using the server's host
$apiBaseUrl = "http://backend:80/api/index.php?route=users"; // Assumes 'backend' is resolvable in the Docker network

$error = null;

function redirectToIndexPage() {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['back_to_login'])) {
        // Handle back to login button
        redirectToIndexPage();
    } else {
        // Safely retrieve POST data and validate it
        $firstName = $_POST['first_name'] ?? null;
        $lastName = $_POST['last_name'] ?? null;
        $email = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;
       

        // Validate input fields
        if (!$email || !$password || !$firstName || !$lastName) {
            $error = "All fields are required.";
        } else {
            // Prepare data for POST request
            $postData = json_encode([
                'email' => $email,
                'password' => $password,
                'first_name' => $firstName,
                'last_name' => $lastName,
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
                if ($response['success']) {
                    // Redirect to index page after successful registration
                    redirectToIndexPage();
                } else {
                    $error = $response['message'] ?? "Failed to register the user.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Student</title>
</head>
<body>
    <h1>Register Student</h1>

    <!-- Display error message if any -->
    <?php if (!empty($error)): ?>
        <div class="error" style="color: red;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="index.php" method="POST">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br>
        <label for="first_name">First Name:</label>
        <input type="text" id="first_name" name="first_name" required>
        <br>
        <label for="last_name">Last Name:</label>
        <input type="text" id="last_name" name="last_name" required>
        <br>
        <button type="submit" name="register">Register</button>
    </form>

    <form method="POST" style="margin-top: 10px;">
        <button type="submit" name="back_to_login">Back to Login</button>
    </form>
</body>
</html>