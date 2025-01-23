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
        $userType = $_POST['user_type'] ?? null;
        $studyProgram = $_POST['study_program'] ?? null;
        $cohortYear = $_POST['cohort_year'] ?? null;

        // Validate input fields
        if (!$firstName || !$lastName || !$email || !$password || !$userType || !$studyProgram || !$cohortYear) {
            $error = "All fields are required.";
        } else {
            // Prepare data for POST request
            $postData = json_encode([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'password' => $password,
                'user_type' => $userType,
                'study_program' => $studyProgram,
                'cohort_year' => $cohortYear,
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
                   // redirectToIndexPage();
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

    <form action="registerStudent.php" method="POST">
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
        <label for="user_type">User Type:</label>
        <select id="user_type" name="user_type" required>
            <option value="student">Student</option>
            <option value="lecturer">Lecturer</option>
            <option value="admin">Admin</option>
        </select>
        <br>
        <label for="study_program">Study Program:</label>
        <input type="text" id="study_program" name="study_program" required>
        <br>
        <label for="cohort_year">Cohort Year:</label>
        <input type="number" id="cohort_year" name="cohort_year" required>
        <br>
        <button type="submit" name="register">Register</button>
    </form>

    <form method="POST" style="margin-top: 10px;">
        <button type="submit" name="back_to_login">Back to Login</button>
    </form>
</body>
</html>