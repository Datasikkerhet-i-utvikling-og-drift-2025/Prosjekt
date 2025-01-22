<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: /loggInn.php");
    exit;
}

$user = $_SESSION['user'];

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Safely retrieve POST data and validate it
    $firstName = $_POST['first_name'] ?? null;
    $lastName = $_POST['last_name'] ?? null;
    $studyProgram = $_POST['study_program'] ?? null;
    $cohortYear = $_POST['cohort_year'] ?? null;

    // Validate input fields
    if (!$firstName || !$lastName || !$studyProgram || !$cohortYear) {
        $error = "All fields are required.";
    } else {
        // Prepare data for POST request
        $postData = json_encode([
            'student_id' => $user['user_id'],
            'first_name' => $firstName,
            'last_name' => $lastName,
            'study_program' => $studyProgram,
            'cohort_year' => $cohortYear,
        ]);

        // Create HTTP context for the POST request
        $apiBaseUrl = "http://backend:80/api/index.php?route=students";
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
                $error = $response['message'] ?? "Failed to register student.";
            } else {
                header("Location: user.php");
                exit;
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
    <title>User Page</title>
    <link rel="stylesheet" href="style.css">
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
        <p><strong>User ID:</strong> <?= htmlspecialchars($user['user_id']) ?></p>
        <p><strong>User Type:</strong> <?= htmlspecialchars($user['user_type']) ?></p>
        <p><strong>Created At:</strong> <?= htmlspecialchars($user['created_at']) ?></p>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
        <h1>Complete Your Student Profile</h1>

        <!-- Display error message if any -->
        <?php if (!empty($error)): ?>
            <div class="error" style="color: red;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" required>

            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" required>

            <label for="study_program">Study Program:</label>
            <input type="text" id="study_program" name="study_program" required>

            <label for="cohort_year">Cohort Year:</label>
            <input type="number" id="cohort_year" name="cohort_year" required>

            <button type="submit">Complete Profile</button>
        </form>
    </div>
</body>
</html>