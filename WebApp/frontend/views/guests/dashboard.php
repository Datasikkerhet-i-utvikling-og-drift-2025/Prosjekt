<?php
use managers\ApiManager;
session_start();
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';

// Sanitize output
function sanitize($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$courseId = $_GET['course_id'] ?? null;
$authorized = $courseId !== null && isset($_SESSION['authorized_courses']) && isset($_SESSION['authorized_courses'][$courseId]);


// Handle PIN submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo '<pre>';
    print_r($_POST);
    echo '</pre>';

    $pin = $_POST['pin'] ?? '';
    try {
        $apiManager = new ApiManager();
        $responseData = $apiManager->get('/api/v1/guest/authorize', ['pin' => $pin, 'course_id' => $courseId]);

        if ($responseData['success'] === true) {
            $_SESSION['authorized_courses'][$responseData['data']['course_id']] = true;
            header('Location: /guests/dashboard?course_id=' . $responseData['data']['course_id']);
            exit;
        } else {
            $_SESSION['errors'] = $responseData['errors'] ?? ['Invalid PIN.'];
        }
    } catch (Throwable $e) {
        $_SESSION['errors'] = ['Unexpected error: ' . $e->getMessage()];
    }
}

if (isset($_SESSION['errors'])) {
    echo "<p style='color:red;'>" . implode("<br>", $_SESSION['errors']) . "</p>";
    unset($_SESSION['errors']);
}

if ($authorized) {
    // Fetch course details if authorized
    try {
        $apiManager = new ApiManager();
        $response = $apiManager->get('/api/v1/guest/details', ['course_id' => $courseId]);

        if (!$response) {
            echo "<p>Failed to fetch course details. Please try again later.</p>";
            include __DIR__ . '/../partials/footer.php';
            exit;
        }

        $data = json_decode($response, true);
        if (!$data['success']) {
            echo "<p>Error: " . sanitize($data['message']) . "</p>";
            include __DIR__ . '/../partials/footer.php';
            exit;
        }

        $course = $data['data']['course'];
        $lecturer = $data['data']['lecturer'];
        $messages = $data['data']['messages'];
        $comments = $data['data']['comments'];
    } catch (Throwable $e) {
        $_SESSION['errors'] = ['Error fetching course details: ' . $e->getMessage()];
        include __DIR__ . '/../partials/footer.php';
        exit;
    }
}
?>

<?php if (!$authorized): ?>
    <!-- PIN input form -->
    <form method="POST" action="">
        <label>Enter PIN to view course messages:</label>
        <input type="text" name="pin" required>
        <input type="hidden" name="courseId" value="<?= sanitize($courseId ?? '') ?>">
        <button type="submit">Submit</button>
    </form>
<?php else: ?>
    <!-- Course details displayed after successful PIN -->
    <div class="container">
        <h1>Course Messages</h1>
        <p><strong>Course:</strong> <?= sanitize($course['name']) ?> (<?= sanitize($course['code']) ?>)</p>

        <h2>Lecturer</h2>
        <?php if ($lecturer): ?>
            <p><strong>Name:</strong> <?= sanitize($lecturer['name']) ?></p>
            <?php if (!empty($lecturer['image_path'])): ?>
                <img src="<?= sanitize($lecturer['image_path']) ?>" alt="Lecturer Image" width="120">
            <?php endif; ?>
        <?php else: ?>
            <p>No lecturer info available.</p>
        <?php endif; ?>

        <h2>Messages</h2>
        <?php if (!empty($messages)): ?>
            <?php foreach ($messages as $message): ?>
                <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
                    <p><strong>Message:</strong> <?= sanitize($message['content']) ?></p>
                    <?php if ($message['is_reported']): ?>
                        <p style="color: red;"><strong>Reported</strong></p>
                    <?php endif; ?>

                    <h4>Comments:</h4>
                    <ul>
                        <?php foreach ($comments as $comment): ?>
                            <?php if ($comment['message_id'] == $message['id']): ?>
                                <li><strong><?= sanitize($comment['guest_name']) ?>:</strong> <?= sanitize($comment['content']) ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No messages for this course yet.</p>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../partials/footer.php'; ?>