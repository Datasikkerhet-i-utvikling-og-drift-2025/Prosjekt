<?php
$host = 'mysql';
$db = 'database';
$user = 'admin';
$pass = 'admin';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Safely retrieve POST data and validate it
    $email = $_POST['email'] ?? null;
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $first_name = $_POST['first_name'] ?? null;
    $last_name = $_POST['last_name'] ?? null;
    $user_type = $_POST['user_type'] ?? null;
    $study_program = $_POST['study_program'] ?? null;
    $cohort_year = $_POST['cohort_year'] ?? null;

    if (empty($email) || empty($password) || empty($first_name) || empty($last_name) || empty($user_type) || empty($study_program) || empty($cohort_year)) {
        http_response_code(400);
        echo json_encode(['error' => 'All fields are required']);
        exit;
    }
    echo "<script>console.log('Debug Objects: " . "try to insert user data" ."' );</script>";
    // Insert user credentials into users table
    $stmt = $conn->prepare("INSERT INTO users (email, password_hash, user_type) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $password, $user_type);
    $stmt->execute();
    if ($stmt->get_result()) {
        echo "<script>console.log('Debug Objects: " . "Executed data" ."' );</script>";
        // Get the inserted user ID
        $stmt = $conn->prepare("SELECT MAX(LAST_INSERT_ID(user_id)) AS user_id FROM users");
            $stmt->execute();
            $result = $stmt->get_result();
            $user_id = $result->fetch_assoc();
            echo "<script>console.log('Debug Objects: " . $user_id ."' );</script>";
        // Insert student details into students table using the user_id as student_id
        $stmt = $conn->prepare("INSERT INTO students (student_id, first_name, last_name, study_program, cohort_year) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", $user_id, $first_name, $last_name, $study_program, $cohort_year);
        $stmt->execute();
        if ($stmt->get_result()) {
            // Registration successful, return a success message
            echo json_encode(['message' => 'Student registered successfully.']);
            echo 'Student registered successfully.';
        } else {
            error_log("Failed to insert student details: " . $stmt->error);
            http_response_code(500);
            echo json_encode(['error' => 'Failed to insert student details']);
        }
    } else {
        error_log("Failed to insert user credentials: " . $stmt->error);
        http_response_code(500);
        echo json_encode(['error' => 'Failed to insert user credentials']);
    }
            

            


    $stmt->close();
    exit;
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

$conn->close();
?>