<?php include '../src/views/partials/header.php'; ?>

<div class="container">
    <h1>Messages for Course: <?php echo htmlspecialchars($_GET['course_code'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?></h1>

    <!-- Error Message Placeholder -->
    <?php if (!empty($_GET['error'])): ?>
        <div id="error-message" style="color: red;">
            <?php echo htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <!-- Messages List -->
    <div id="messages-container">
        <?php
        // Get course code and pin from the query string
        $courseCode = $_GET['course_code'] ?? null;
        $pinCode = $_GET['pin_code'] ?? null;

        if (!$courseCode || !$pinCode) {
            echo '<p>Invalid course code or PIN.</p>';
        } else {
            try {
                // Fetch messages from the database
                $db = new \db\Database();
                $pdo = $db->getConnection();

                $stmt = $pdo->prepare("SELECT * FROM messages WHERE course_code = :course_code AND pin_code = :pin_code");
                $stmt->execute([
                    ':course_code' => $courseCode,
                    ':pin_code' => $pinCode
                ]);

                $messages = $stmt->fetchAll();

                if (empty($messages)) {
                    echo '<p>No messages available for this course.</p>';
                } else {
                    foreach ($messages as $message) {
                        ?>
                        <div class="message-item">
                            <p><strong>Message:</strong> <?php echo htmlspecialchars($message['content'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>Reply:</strong> <?php echo htmlspecialchars($message['reply'] ?? 'No reply yet', ENT_QUOTES, 'UTF-8'); ?></p>
                            <div class="message-actions">
                                <a href="/guest/report-message.php?message_id=<?php echo $message['id']; ?>">Report</a>
                                <a href="/guest/comment.php?message_id=<?php echo $message['id']; ?>">Comment</a>
                            </div>
                            <hr>
                        </div>
                        <?php
                    }
                }
            } catch (Exception $e) {
                echo '<p style="color: red;">Error loading messages. Please try again later.</p>';
            }
        }
        ?>
    </div>
</div>

<?php include '../src/views/partials/footer.php'; ?>
