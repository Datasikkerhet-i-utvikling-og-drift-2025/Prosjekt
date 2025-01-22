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
    // Handle sending a new message
    $course_id = $_POST['course_id'];
    $student_id = $_POST['student_id'];
    $message_text = $_POST['message_text'];
    $is_anonymous = $_POST['is_anonymous'];

    if (empty($course_id) || empty($student_id) || empty($message_text)) {
        http_response_code(400);
        echo json_encode(['error' => 'Course ID, Student ID, and Message Text are required']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO messages (course_id, student_id, message_text, is_anonymous) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iisi", $course_id, $student_id, $message_text, $is_anonymous);

    if ($stmt->execute()) {
        echo json_encode(['message' => 'Message sent successfully.']);
    } else {
        echo json_encode(['error' => 'Error: ' . $stmt->error]);
    }

    $stmt->close();
    exit;
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Handle fetching messages
    $sql = "SELECT message_id, course_id, student_id, message_text, is_anonymous, created_at FROM messages";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        echo json_encode($messages);
    } else {
        echo json_encode([]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

$conn->close();
?>