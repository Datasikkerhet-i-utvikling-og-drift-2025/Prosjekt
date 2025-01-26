<?php
session_start();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header('Location: /auth/login.php');
    exit;
}

// Get course information from query parameters
$courseId = htmlspecialchars($_GET['course_id'] ?? '', ENT_QUOTES, 'UTF-8');
if (empty($courseId)) {
    echo 'Course ID is required.';
    exit;
}

// Handle form submission
$messageSent = false;
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../../helpers/Database.php';

    try {
        $db = new \db\Database();
        $pdo = $db->getConnection();

        $messageContent = htmlspecialchars($_POST['message_content'], ENT_QUOTES, 'UTF-8');
        $studentId = $_SESSION['user']['id'];

        if (empty($messageContent)) {
            $errorMessage = 'Message content cannot be empty.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO messages (course_id, student_id, content, created_at) VALUES (:course_id, :student_id, :content, NOW())");
            $stmt->execute([
                ':course_id' => $courseId,
                ':student_id' => $studentId,
                ':content' => $messageContent,
            ]);

            $messageSent = true;
        }
    } catch (Exception $e) {
        $errorMessage = 'An error occurred while sending the message. Please try again later.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Message - Feedback System</title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Include your CSS -->
</head>
<body>
<?php include '../partials/navbar.php'; ?> <!-- Include Navbar -->

<div class="container">
    <h1>Send a Message</h1>
    <p>Send a message to the lecturer for this course. You will remain anonymous.</p>

    <!-- Display Success or Error Message -->
    <?php if ($messageSent): ?>
        <div style="color: green;">Message sent successfully!</div>
    <?php elseif (!empty($errorMessage)): ?>
        <div style="color: red;"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <!-- Send Message Form -->
    <form action="" method="POST">
        <input type="hidden" name="course_id" value="<?php echo $courseId; ?>" />

        <div class="form-group">
            <label for="message_content">Your Message</label>
            <textarea id="message_content" name="message_content" rows="4" placeholder="Type your message here..." required><?php echo htmlspecialchars($_POST['message_content'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>

        <button type="submit">Send Message</button>
    </form>
</div>

<?php include '../partials/footer.php'; ?>
</body>
</html>
