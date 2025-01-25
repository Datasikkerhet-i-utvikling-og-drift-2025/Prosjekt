<?php
session_start();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'lecturer') {
    header('Location: /auth/login.php');
    exit;
}

// Get course information from query parameters
$courseId = htmlspecialchars($_GET['course_id'] ?? '', ENT_QUOTES, 'UTF-8');
if (empty($courseId)) {
    echo 'Course ID is required.';
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Read Messages - Feedback System</title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Include your CSS -->
</head>
<body>
<?php include '../src/views/partials/navbar.php'; ?> <!-- Include Navbar -->

<div class="container">
    <h1>Messages for Course ID: <?php echo $courseId; ?></h1>
    <p>Below are the messages sent by students for this course.</p>

    <!-- Error Message Placeholder -->
    <div id="error-message" style="color: red; display: none;"></div>

    <!-- Messages Section -->
    <div id="messages-container">
        <p>Loading messages...</p>
    </div>
</div>

<script>
    // Load messages via API
    async function loadMessages() {
        try {
            const response = await fetch(`/lecturer/messages?course_id=${<?php echo $courseId; ?>}`, {
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token') // Assuming JWT for authentication
                }
            });
            const result = await response.json();

            const messagesContainer = document.getElementById('messages-container');
            messagesContainer.innerHTML = '';

            if (response.ok) {
                if (result.data.length === 0) {
                    messagesContainer.innerHTML = '<p>No messages found for this course.</p>';
                    return;
                }

                result.data.forEach(message => {
                    const messageDiv = document.createElement('div');
                    messageDiv.classList.add('message-item');

                    messageDiv.innerHTML = `
                            <p><strong>Message:</strong> ${message.content}</p>
                            <p><strong>Sent At:</strong> ${message.created_at}</p>
                            <p><strong>Response:</strong> ${message.reply || 'No response yet'}</p>
                            <a href="/lecturer/reply.php?message_id=${message.id}" class="btn">Reply</a>
                            <hr>
                        `;
                    messagesContainer.appendChild(messageDiv);
                });
            } else {
                messagesContainer.innerHTML = `<p>${result.message || 'Failed to load messages.'}</p>`;
            }
        } catch (error) {
            console.error('Error loading messages:', error);
            const errorMessage = document.getElementById('error-message');
            errorMessage.textContent = 'Unable to load messages. Please try again later.';
            errorMessage.style.display = 'block';
        }
    }

    // Load messages on page load
    loadMessages();
</script>
</body>
</html>
