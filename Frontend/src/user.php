<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['user'];

// Handle logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    // Clear the session
    session_unset();
    session_destroy();
    // Redirect to index.php
    header("Location: index.php");
    exit();
}

// Handle role activation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['activate_role'])) {
    // Prepare data for POST request
    $postData = json_encode([
        'user_id' => $user['user_id'],
        'study_program' => $_POST['study_program'] ?? null,
        'cohort_year' => $_POST['cohort_year'] ?? null,
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
    $apiBaseUrl = "http://backend:80/api/index.php?route=users";
    $result = @file_get_contents($apiBaseUrl, false, $context);

    if ($result === false) {
        $error = "Error connecting to the backend. Please check the API configuration.";
    } else {
        $response = json_decode($result, true);
        if ($response['success']) {
            $user['study_program'] = $_POST['study_program'];
            $user['cohort_year'] = $_POST['cohort_year'];
            $_SESSION['user'] = $user;
            $success = "Role activated successfully.";
        } else {
            $error = $response['message'] ?? "Failed to activate the role.";
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
        .logout-form, .role-form {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="user-info">
    <h2>Welcome, <?= htmlspecialchars($user['first_name']) ?> <?= htmlspecialchars($user['last_name']) ?></h2>
    <p>Email: <?= htmlspecialchars($user['email']) ?></p>
    <p>Created At: <?= htmlspecialchars($user['created_at']) ?></p>
    <?php if (isset($user['study_program']) && isset($user['cohort_year'])): ?>
        <p>Role: Student</p>
        <p>Study Program: <?= htmlspecialchars($user['study_program']) ?></p>
        <p>Cohort Year: <?= htmlspecialchars($user['cohort_year']) ?></p>
    <?php else: ?>
        <form class="role-form" action="" method="POST">
            <label for="role">Select Role:</label>
            <select id="role" name="role" required>
                <option value="">Select a role</option>
                <option value="student">Student</option>
            </select>
            <br>
            <div id="student-fields" style="display: none;">
                <label for="study_program">Study Program:</label>
                <input type="text" id="study_program" name="study_program" required>
                <br>
                <label for="cohort_year">Cohort Year:</label>
                <input type="number" id="cohort_year" name="cohort_year" required>
            </div>
            <button type="submit" name="activate_role">Activate Role</button>
        </form>
    <?php endif; ?>
    <form class="logout-form" action="" method="POST">
        <button type="submit" name="logout">Logout</button>
    </form>
    <?php if (!empty($error)): ?>
        <div class="error" style="color: red;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="success" style="color: green;"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
</div>
<script>
    document.getElementById('role').addEventListener('change', function() {
        var role = this.value;
        document.getElementById('student-fields').style.display = role === 'student' ? 'block' : 'none';
    });
</script>
</body>
</html>