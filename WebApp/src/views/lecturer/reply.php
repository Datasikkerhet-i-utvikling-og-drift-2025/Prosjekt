<?php
session_start();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'lecturer') {
    header('Location: /auth/login.php');
    exit;
}

// Get message information from query parameters
$messageId = htmlspecialchars($_GET['message_id'] ?? '', ENT_QUOTES, 'UTF-8');
if (empty($messageId)) {
    echo 'Message ID is required.';
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reply to Message - Feedback System</title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Include your CSS -->
</head>
<body>
<?php include '../src/views/partials/navbar.php'; ?> <!-- Include Navbar -->

<div class="container">
    <h1>Reply to Message</h1>
    <p>Provide a reply to the student's message below.</p>

    <!-- Error Message Placeholder -->
    <div id="error-message" style="color: red; display: none;"></div>
    <div id="success-message" style="color: green; display: none;"></div>

    <!-- Message Content -->
    <div id="message-container">
        <p>Loading message...</p>
    </div>

    <!-- Reply Form -->
    <form id="reply-form" action="/lecturer/messages/reply" method="POST">
        <input type="hidden" id="message_id" name="message_id" value="<?php echo $messageId; ?>" />

        <div class="form-group">
            <label for="reply_content">Your Reply</label>
            <textarea id="reply_content" name="reply_content" rows="4" placeholder="Type your reply here..." required></textarea>
        </div>

        <button type="submit">Send Reply</button>
    </form>
</div>

<script>
    // Load the message via API
    async function loadMessage() {
        try {
            const response = await fetch(`/lecturer/messages/view?message_id=${<?php echo $messageId; ?>}`, {
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token') // Assuming JWT for authentication
                }
            });
            const result = await response.json();

            const messageContainer = document.getElementById('message-container');
            messageContainer.innerHTML = '';

            if (response.ok) {
                const message = result.data;
                messageContainer.innerHTML = `
                        <p><strong>Message:</strong> ${message.content}</p>
                        <p><strong>Sent At:</strong> ${message.created_at}</p>
                        <p><strong>Current Reply:</strong> ${message.reply || 'No reply yet'}</p>
                    `;
            } else {
                messageContainer.innerHTML = `<p>${result.message || 'Failed to load the message.'}</p>`;
            }
        } catch (error) {
            console.error('Error loading message:', error);
            const errorMessage = document.getElementById('error-message');
            errorMessage.textContent = 'Unable to load the message. Please try again later.';
            errorMessage.style.display = 'block';
        }
    }

    // Handle form submission
    const form = document.getElementById('reply-form');
    form.addEventListener('submit', async (e) => {
        e.preventDefault(); // Prevent default form submission

        const messageId = document.getElementById('message_id').value;
        const replyContent = document.getElementById('reply_content').value;

        try {
            const response = await fetch('/lecturer/messages/reply', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + localStorage.getItem('token') // Assuming JWT
                },
                body: JSON.stringify({ message_id: messageId, reply: replyContent }),
            });

            const result = await response.json();

            if (response.ok) {
                const successMessage = document.getElementById('success-message');
                successMessage.textContent = 'Reply sent successfully!';
                successMessage.style.display = 'block';

                // Clear the form
                form.reset();
                loadMessage(); // Reload the message to reflect the new reply
            } else {
                const errorMessage = document.getElementById('error-message');
                errorMessage.textContent = result.message || 'An error occurred while sending your reply.';
                errorMessage.style.display = 'block';
            }
        } catch (error) {
            console.error('Error sending reply:', error);
            const errorMessage = document.getElementById('error-message');
            errorMessage.textContent = 'Unable to connect to the server. Please try again later.';
            errorMessage.style.display = 'block';
        }
    });

    // Load the message on page load
    loadMessage();
</script>
</body>
</html>
