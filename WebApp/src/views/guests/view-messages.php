<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Messages - Feedback System</title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Include your CSS -->
</head>
<body>
<div class="container">
    <h1>Messages for Course: <?php echo htmlspecialchars($_GET['course_code'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?></h1>

    <!-- Error Message Placeholder -->
    <div id="error-message" style="color: red; display: none;"></div>

    <!-- Messages List -->
    <div id="messages-container">
        <!-- Messages will be dynamically loaded here -->
    </div>
</div>

<script>
    // Get course code and PIN from the query string
    const urlParams = new URLSearchParams(window.location.search);
    const courseCode = urlParams.get('course_code');
    const pinCode = urlParams.get('pin_code');

    // Load messages via API
    async function loadMessages() {
        try {
            const response = await fetch(`/guest/messages/view?course_code=${courseCode}&pin_code=${pinCode}`);
            const result = await response.json();

            if (response.ok) {
                const messagesContainer = document.getElementById('messages-container');
                messagesContainer.innerHTML = '';

                if (result.data.length === 0) {
                    messagesContainer.innerHTML = '<p>No messages available for this course.</p>';
                    return;
                }

                // Render messages
                result.data.forEach(message => {
                    const messageDiv = document.createElement('div');
                    messageDiv.classList.add('message-item');

                    messageDiv.innerHTML = `
                            <p><strong>Message:</strong> ${message.content}</p>
                            <p><strong>Reply:</strong> ${message.reply || 'No reply yet'}</p>
                            <div class="message-actions">
                                <a href="/guest/report-message.php?message_id=${message.id}">Report</a>
                                <a href="/guest/comment.php?message_id=${message.id}">Comment</a>
                            </div>
                            <hr>
                        `;
                    messagesContainer.appendChild(messageDiv);
                });
            } else {
                const errorMessage = document.getElementById('error-message');
                errorMessage.textContent = result.message || 'Failed to load messages.';
                errorMessage.style.display = 'block';
            }
        } catch (error) {
            console.error('Error loading messages:', error);
            const errorMessage = document.getElementById('error-message');
            errorMessage.textContent = 'Unable to connect to the server. Please try again later.';
            errorMessage.style.display = 'block';
        }
    }

    // Load messages on page load
    loadMessages();
</script>
</body>
</html>
