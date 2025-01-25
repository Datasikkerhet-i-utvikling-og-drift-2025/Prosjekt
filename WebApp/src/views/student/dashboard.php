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
    <title>Student Dashboard - Feedback System</title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Include your CSS -->
</head>
<body>
<?php include '../src/views/partials/navbar.php'; ?> <!-- Include Navbar -->

<div class="container">
    <h1>Welcome, <?php echo htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8'); ?>!</h1>
    <p>This is your dashboard. Here you can view your messages and explore courses.</p>

    <!-- Error Message Placeholder -->
    <div id="error-message" style="color: red; display: none;"></div>

    <!-- Messages Section -->
    <section>
        <h2>Your Messages</h2>
        <div id="messages-container">
            <p>Loading messages...</p>
        </div>
    </section>

    <!-- Courses Section -->
    <section>
        <h2>Available Courses</h2>
        <div id="courses-container">
            <p>Loading courses...</p>
        </div>
    </section>
</div>

<script>
    // Load messages via API
    async function loadMessages() {
        try {
            const response = await fetch('/student/messages');
            const result = await response.json();

            const messagesContainer = document.getElementById('messages-container');
            messagesContainer.innerHTML = '';

            if (response.ok) {
                if (result.data.length === 0) {
                    messagesContainer.innerHTML = '<p>No messages found.</p>';
                    return;
                }

                result.data.forEach(message => {
                    const messageDiv = document.createElement('div');
                    messageDiv.classList.add('message-item');

                    messageDiv.innerHTML = `
                            <p><strong>Message:</strong> ${message.content}</p>
                            <p><strong>Reply:</strong> ${message.reply || 'No reply yet'}</p>
                            <hr>
                        `;
                    messagesContainer.appendChild(messageDiv);
                });
            } else {
                messagesContainer.innerHTML = `<p>${result.message || 'Failed to load messages.'}</p>`;
            }
        } catch (error) {
            console.error('Error loading messages:', error);
            document.getElementById('messages-container').innerHTML = '<p>Unable to load messages. Please try again later.</p>';
        }
    }

    // Load courses via API
    async function loadCourses() {
        try {
            const response = await fetch('/student/courses');
            const result = await response.json();

            const coursesContainer = document.getElementById('courses-container');
            coursesContainer.innerHTML = '';

            if (response.ok) {
                if (result.data.length === 0) {
                    coursesContainer.innerHTML = '<p>No courses found.</p>';
                    return;
                }

                result.data.forEach(course => {
                    const courseDiv = document.createElement('div');
                    courseDiv.classList.add('course-item');

                    courseDiv.innerHTML = `
                            <p><strong>Course Code:</strong> ${course.code}</p>
                            <p><strong>Course Name:</strong> ${course.name}</p>
                            <a href="/student/send-message.php?course_id=${course.id}" class="btn">Send a Message</a>
                            <hr>
                        `;
                    coursesContainer.appendChild(courseDiv);
                });
            } else {
                coursesContainer.innerHTML = `<p>${result.message || 'Failed to load courses.'}</p>`;
            }
        } catch (error) {
            console.error('Error loading courses:', error);
            document.getElementById('courses-container').innerHTML = '<p>Unable to load courses. Please try again later.</p>';
        }
    }

    // Load messages and courses on page load
    loadMessages();
    loadCourses();
</script>
</body>
</html>
