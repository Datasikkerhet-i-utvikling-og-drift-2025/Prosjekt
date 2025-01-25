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
<?php include '../src/views/partials/navbar.php'; ?> <!-- Include Navbar -->

<div class="container">
    <h1>Send a Message</h1>
    <p>Send a message to the lecturer for this course. You will remain anonymous.</p>

    <!-- Error Message Placeholder -->
    <div id="error-message" style="color: red; display: none;"></div>
    <div id="success-message" style="color: green; display: none;"></div>

    <!-- Send Message Form -->
    <form id="send-message-form" action="/student/messages/send" method="POST">
        <input type="hidden" id="course_id" name="course_id" value="<?php echo $courseId; ?>" />

        <div class="form-group">
            <label for="message_content">Your Message</label>
            <textarea id="message_content" name="message_content" rows="4" placeholder="Type your message here..." required></textarea>
        </div>

        <button type="submit">Send Message</button>
    </form>
</div>

<script>
    // Handle form submission
    const form = document.getElementById('send-message-form');
    form.addEventListener('submit', async (e) => {
        e.preventDefault(); // Prevent default form submission

        const courseId = document.getElementById('course_id').value;
        const messageContent = document.getElementById('message_content').value;

        try {
            const response = await fetch('/student/messages/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + localStorage.getItem('token') // Assuming you're using JWT
                },
                body: JSON.stringify({ course_id: courseId, content: messageContent }),
            });

            const result = await response.json();

            if (response.ok) {
                const successMessage = document.getElementById('success-message');
                successMessage.textContent = 'Message sent successfully!';
                successMessage.style.display = 'block';

                // Clear the form
                form.reset();
            } else {
                const errorMessage = document.getElementById('error-message');
                errorMessage.textContent = result.message || 'An error occurred while sending your message.';
                errorMessage.style.display = 'block';
            }
        } catch (error) {
            console.error('Error:', error);
            const errorMessage = document.getElementById('error-message');
            errorMessage.textContent = 'Unable to connect to the server. Please try again later.';
            errorMessage.style.display = 'block';
        }
    });
</script>
</body>
</html>
