<?php
session_start();
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';

// Sanitize output
function sanitize($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$pdo = (new \db\Database())->getConnection();
$pin = $_POST['pin'] ?? null;
$authorized = false;
$course = null;

// Handle PIN submission
if ($pin) {
    $stmt = $pdo->prepare("SELECT id, code, name, pin_code, lecturer_id FROM courses WHERE pin_code = :pin_code");
    $stmt->execute([':pin_code' => $pin]);
    $course = $stmt->fetch();

    if ($course) {
        $_SESSION['authorized_courses'][$course['id']] = true;
    }
}

if ($course && !empty($_SESSION['authorized_courses'][$course['id']])) {
    $authorized = true;
}

if (!$authorized) {
    echo "<form method='POST'>
            <label>Enter PIN to view course messages:</label>
            <input type='text' name='pin' required>
            <button type='submit'>Submit</button>
          </form>";
    include __DIR__ . '/../partials/footer.php';
    exit;
}

// Fetch lecturer details
$stmt = $pdo->prepare("SELECT name, image_path FROM users WHERE id = :lecturer_id AND role = 'lecturer'");
$stmt->execute([':lecturer_id' => $course['lecturer_id']]);
$lecturer = $stmt->fetch();

// Fetch messages for the course
$stmt = $pdo->prepare("SELECT id, content FROM messages WHERE course_id = :course_id");
$stmt->execute([':course_id' => $course['id']]);
$messages = $stmt->fetchAll();
?>
<div class="container">
    <h1>Course Messages</h1>
    <p><strong>Course:</strong> <?= sanitize($course['code']) ?> - <?= sanitize($course['name']) ?></p>
    <p><strong>Lecturer:</strong> <?= sanitize($lecturer['name'] ?? 'Unknown') ?></p>
    <img src="<?= sanitize($lecturer['image_path'] ?? 'uploads/profile_pictures/hiof.jpg') ?>" alt="Lecturer Image" width="100" height="100">

    <?php foreach ($messages as $message): ?>
        <div class="message-box">
            <p><strong>Message:</strong> <?= sanitize($message['content']) ?></p>
            <a href="report-message.php?message_id=<?= $message['id'] ?>" class="btn btn-danger">Report Message</a>
            
            <h3>Leave a Comment</h3>
            <form action="/guest/messages/comment" method="POST">
                <input type="hidden" name="message_id" value="<?= $message['id'] ?>">
                <label>Your Name (Optional):</label>
                <input type="text" name="guest_name">
                <label>Your Comment:</label>
                <textarea name="comment" required></textarea>
                <button type="submit">Submit</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
