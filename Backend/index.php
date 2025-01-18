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
        $userType = $_POST['user_type'];

        // Validate user input
        if (empty($email) || empty($password) || empty($userType)) {
            $error = "All fields are required.";
        } else {
            // Hash the password securely
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);

            // Insert the user into the database
            $sql = "INSERT INTO users (email, password_hash, user_type) VALUES (:email, :password_hash, :user_type)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':email' => $email,
                ':password_hash' => $passwordHash,
                ':user_type' => $userType
            ]);

            $success = "User registered successfully!";
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
    <title>Testing User Registration!!</title>
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
        input, select, button {
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
<h1>Testing User Registration</h1>

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

    <label for="user_type">User Type:</label>
    <select name="user_type" id="user_type" required>
        <option value="student">Student</option>
        <option value="lecturer">Lecturer</option>
        <option value="admin">Admin</option>
    </select>

    <button type="submit">Register</button>
</form>
</body>
</html>

