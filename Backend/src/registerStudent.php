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


    if (empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
        http_response_code(400);
        echo json_encode(['error' => 'All fields are required']);
        exit;
    }
}
?>