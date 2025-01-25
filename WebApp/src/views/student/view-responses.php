<?php
session_start();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header('Location: /auth/login.php');
    exit;
}

// Get the student's name for display
$studentName = $_SESSION['user']['name'] ?? 'Student';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Responses - Feedback System</title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Include your CSS -->
</head>
<body>
<?php include '../src/views/partials/navbar.php'; ?> <!-- Include Navbar -->

<div class="container">
    <h1>Responses to Your Messages</h1>
    <p>Here you can view all your messages and any responses from the lecturers.</p>

    <!-- Error Message Placeholder -->
    <div id="error-message" style="color: red; display: none;"></div>

    <!-- Messages and Responses Section -->
    <div id="responses-container">
        <p>Loading your messages and responses...</p>
    </div>
</div>

<script>
    // Load messages with responses via API
    async function loadResponses() {
        try {
            const response = await fetch('/student/messages/responses', {
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token') // Assuming JWT for authentication
                }
            });
            const result = await response.json();

            const responsesContainer = document.getElementById('responses-container');
            responsesContainer.innerHTML = '';

            if (response.ok) {
                if (result.data.length === 0) {
                    responsesContainer.innerHTML = '<p>You have not sent any messages yet.</p>';
                    return;
                }

                // Render messages with responses
                result.data.forEach(message => {
                    const messageDiv = document.createElement('div');
                    messageDiv.classList.add('message-item');

                    messageDiv.innerHTML = `
                            <p><strong>Message:</strong> ${message.content}</p>
                            <p><strong>Response:</strong> ${message.reply || 'No response yet'}</p>
                            <p><strong>Sent At:</strong> ${message.created_at}</p>
                            <hr>
                        `;
                    responsesContainer.appendChild(messageDiv);
                });
            } else {
                responsesContainer.innerHTML = `<p>${result.message || 'Failed to load responses.'}</p>`;
            }
        } catch (error) {
            console.error('Error loading responses:', error);
            document.getElementById('error-message').textContent = 'Unable to connect to the server. Please try again later.';
            document.getElementById('error-message').style.display = 'block';
        }
    }

    // Load responses on page load
    loadResponses();
</script>
</body>
</html>
