<?php
// Database connection details
$host = "MySQL"; // Use Docker container name for MySQL
$username = "admin";
$password = "admin";
$database = "database";

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Validate user input
        if (empty($email) || empty($password)) {
            $error = "All fields are required.";
        } else {
            // Check the user in the database
            $sql = "SELECT * FROM users WHERE email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                $success = "Login successful!";
            } else {
                $error = "Invalid email or password.";
            }
        }
    }
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        form {
            max-width: 400px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background: #f9f9f9;
        }
        input, button {
            display: block;
            width: 100%;
            margin-bottom: 10px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
        .success {
            color: green;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<h1>Login</h1>

<?php if (isset($error)): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php elseif (isset($success)): ?>
    <p class="success"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<form method="POST">
    <label for="email">Email:</label>
    <input type="email" name="email" id="email" required>

    <label for="password">Password:</label>
    <input type="password" name="password" id="password" required>

    <button type="submit">Login</button>
</form>
</body>
</html>