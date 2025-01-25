<?php
session_start();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'lecturer') {
    header('Location: /auth/login.php');
    exit;
}

// Get the lecturer's name for display
$lecturerName = $_SESSION['user']['name'] ?? 'Lecturer';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Dashboard - Feedback System</title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Include your CSS -->
</head>
<body>
<?php include '../src/views/partials/navbar.php'; ?> <!-- Include Navbar -->

<div class="container">
    <h1>Welcome, <?php echo htmlspecialchars($lecturerName, ENT_QUOTES, 'UTF-8'); ?>!</h1>
    <p>This is your dashboard. Here you can manage your courses and view messages from students.</p>

    <!-- Error Message Placeholder -->
    <div id="error-message" style="color: red; display: none;"></div>

    <!-- Courses Section -->
    <section>
        <h2>Your Courses</h2>
        <div id="courses-container">
            <p>Loading your courses...</p>
        </div>
    </section>

    <!-- Messages Section -->
    <section>
        <h2>Student Messages</h2>
        <div id="messages-container">
            <p>Loading messages...</p>
        </div>
    </section>
</div>

<script>
    // Load courses via API
    async function loadCourses() {
        try {
            const response = await fetch('/lecturer/courses', {
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token') // Assuming JWT for authentication
                }
            });
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
                            <a href="/lecturer/read-messages.php?course_id=${course.id}" class="btn">View Messages</a>
                            <hr>
                        `;
                    coursesContainer.appendChild(courseDiv);
                });
            } else {
                coursesContainer.innerHTML = `<p>${result.message || 'Failed to load courses.'}</p>`;
            }
        } catch (error) {
            console.error('Error loading courses:', error);
            document.getElementById('error-message').textContent = 'Unable to load courses. Please try again later.';
            document.getElementById('error-message').style.display = 'block';
        }
    }

    // Load messages via API
    async function loadMessages() {
        try {
            const response = await fetch('/lecturer/messages', {
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token') // Assuming JWT for authentication
                }
            });
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
                            <p><strong>Course:</strong> ${message.course_name}</p>
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
            document.getElementById('error-message').textContent = 'Unable to load messages. Please try again later.';
            document.getElementById('error-message').style.display = 'block';
        }
    }

    // Load courses and messages on page load
    loadCourses();
    loadMessages();
</script>
</body>
</html>
