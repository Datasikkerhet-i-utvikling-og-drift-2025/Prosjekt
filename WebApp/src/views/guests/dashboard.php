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
    <img src="<?= sanitize(preg_replace('/^.*?uploads\//', '/uploads/', $lecturer['image_path'] ?? 'uploads/profile_pictures/hiof.jpg')) ?>" alt="Lecturer Image" width="100" height="100">

    <?php foreach ($messages as $message): ?>
        <hr>
        <div class="message-box">
            <div class="message-header">
                <button class="btn btn-danger btn-small" onclick="showReportModal(<?= $message['id'] ?>)">Report</button>
                <p><strong>Message:</strong> <?= sanitize($message['content']) ?></p>
            </div>
            
            <h3>Leave a Comment Down Below!</h3>
            <form action="/guest/messages/comment" method="POST">
                <input type="hidden" name="message_id" value="<?= $message['id'] ?>">
                <label>Your Name (Optional):</label>
                <input type="text" name="guest_name">
                <label>Your Comment:</label>
                <textarea name="comment" required></textarea>
                <button type="submit" class="btn btn-small" style="padding: 6px 5px; font-size: 12px; width: 40px; height: 30px;">Send</button>

             </form>
        </div>
    <?php endforeach; ?>
</div>

<!-- Report Modal -->
<div id="reportModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeReportModal()">&times;</span>
        <h2>Report Message</h2>
        <form id="reportForm" action="/guest/messages/report" method="POST">
            <input type="hidden" name="message_id" id="reportMessageId">
            <label for="reported_by">Reported By (Optional):</label>
            <input type="text" name="reported_by" id="reported_by">
            <label for="report_reason">Reason for Reporting:</label>
            <textarea name="report_reason" id="report_reason" required></textarea>
            <button type="submit" class="btn btn-danger">Submit Report</button>
        </form>
    </div>
</div>

<script>
function showReportModal(messageId) {
    document.getElementById('reportMessageId').value = messageId;
    document.getElementById('reportModal').style.display = 'block';
}

function closeReportModal() {
    document.getElementById('reportModal').style.display = 'none';
}
</script>

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgb(0,0,0);
    background-color: rgba(0,0,0,0.4);
    padding-top: 60px;
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

.comment-form {
    display: flex;
    flex-direction: column;
}

.comment-form textarea {
    width: 100%;
    margin-bottom: 10px;
}

.comment-form .btn-small {
    align-self: flex-end;
    padding: 5px 10px;
    font-size: 14px;
    width: 10px; /* Set a smaller fixed width */
    height: 25px; /* Set a fixed height */
}

.message-box {
    border-top: 1px solid #ccc; /* Add a line above each message */
    padding-top: 10px;
    margin-top: 10px;
}

.message-header {
    display: flex;
    align-items: center;
}

.message-header .btn {
    margin-right: 10px;
    width: 80px; /* Set a fixed width */
    height: 30px; /* Set a fixed height */
    padding: 5px 10px;
    font-size: 14px;
    text-align: center; /* Center the text */
}
</style>

<?php include __DIR__ . '/../partials/footer.php'; ?>