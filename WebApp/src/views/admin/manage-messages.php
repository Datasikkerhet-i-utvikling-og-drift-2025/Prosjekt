<?php
session_start();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /auth/login.php');
    exit;
}

// Get the admin's name for display
$adminName = $_SESSION['user']['name'] ?? 'Administrator';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Messages - Feedback System</title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Include your CSS -->
</head>
<body>
<?php include '../src/views/partials/navbar.php'; ?> <!-- Include Navbar -->

<div class="container">
    <h1>Manage Messages</h1>
    <p>Below is the list of all messages in the system. You can delete messages or view details for further actions.</p>

    <!-- Error Message Placeholder -->
    <div id="error-message" style="color: red; display: none;"></div>

    <!-- Messages Table -->
    <table class="table">
        <thead>
        <tr>
            <th>Message ID</th>
            <th>Course</th>
            <th>Content</th>
            <th>Reply</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody id="messages-container">
        <tr>
            <td colspan="5">Loading messages...</td>
        </tr>
        </tbody>
    </table>
</div>

<script>
    // Load all messages via API
    async function loadMessages() {
        try {
            const response = await fetch('/admin/messages', {
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token') // Assuming JWT for authentication
                }
            });
            const result = await response.json();

            const messagesContainer = document.getElementById('messages-container');
            messagesContainer.innerHTML = '';

            if (response.ok) {
                if (result.data.length === 0) {
                    messagesContainer.innerHTML = '<tr><td colspan="5">No messages found.</td></tr>';
                    return;
                }

                // Render each message as a table row
                result.data.forEach(message => {
                    const row = document.createElement('tr');

                    row.innerHTML = `
                            <td>${message.id}</td>
                            <td>${message.course_name}</td>
                            <td>${message.content}</td>
                            <td>${message.reply || 'No reply yet'}</td>
                            <td>
                                <a href="/admin/view-message.php?message_id=${message.id}" class="btn btn-view">View</a>
                                <button class="btn btn-delete" data-id="${message.id}">Delete</button>
                            </td>
                        `;

                    messagesContainer.appendChild(row);
                });

                // Attach delete event listeners
                document.querySelectorAll('.btn-delete').forEach(button => {
                    button.addEventListener('click', async (e) => {
                        const messageId = e.target.dataset.id;
                        if (confirm('Are you sure you want to delete this message?')) {
                            await deleteMessage(messageId);
                            loadMessages(); // Reload messages after deletion
                        }
                    });
                });
            } else {
                messagesContainer.innerHTML = `<tr><td colspan="5">${result.message || 'Failed to load messages.'}</td></tr>`;
            }
        } catch (error) {
            console.error('Error loading messages:', error);
            const errorMessage = document.getElementById('error-message');
            errorMessage.textContent = 'Unable to load messages. Please try again later.';
            errorMessage.style.display = 'block';
        }
    }

    // Delete a message via API
    async function deleteMessage(messageId) {
        try {
            const response = await fetch(`/admin/messages/delete/${messageId}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token'), // Assuming JWT
                    'Content-Type': 'application/json'
                }
            });

            const result = await response.json();
            if (!response.ok) {
                alert(result.message || 'Failed to delete the message.');
            } else {
                alert('Message deleted successfully.');
            }
        } catch (error) {
            console.error('Error deleting message:', error);
            alert('Unable to delete the message. Please try again later.');
        }
    }

    // Load messages on page load
    loadMessages();
</script>

<style>
    .container {
        max-width: 1000px;
        margin: 50px auto;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: #f9f9f9;
    }

    h1 {
        text-align: center;
        margin-bottom: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    th, td {
        padding: 10px;
        text-align: left;
        border: 1px solid #ddd;
    }

    th {
        background-color: #f4f4f4;
    }

    .btn {
        display: inline-block;
        padding: 5px 10px;
        background-color: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-size: 14px;
    }

    .btn:hover {
        background-color: #0056b3;
    }

    .btn-delete {
        background-color: #dc3545;
    }

    .btn-delete:hover {
        background-color: #a71d2a;
    }

    .btn-view {
        background-color: #17a2b8;
    }

    .btn-view:hover {
        background-color: #117a8b;
    }
</style>
</body>
</html>
