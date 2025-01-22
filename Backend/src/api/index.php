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
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $userType = $_POST['user_type'];

    $stmt = $conn->prepare("INSERT INTO users (email, password_hash, user_type) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $password, $userType);

    if ($stmt->execute()) {
        echo "User added successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    exit;
} else {
    $sql = "SELECT user_id, email, user_type, created_at FROM users";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        echo json_encode($users);
    } else {
        echo json_encode([]);
    }
}

$conn->close();
