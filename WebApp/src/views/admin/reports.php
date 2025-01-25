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
    <title>Manage Reports - Feedback System</title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Include your CSS -->
</head>
<body>
<?php include '../src/views/partials/navbar.php'; ?> <!-- Include Navbar -->

<div class="container">
    <h1>Manage Reports</h1>
    <p>Below is the list of reported messages. Review the reports and take appropriate actions.</p>

    <!-- Error Message Placeholder -->
    <div id="error-message" style="color: red; display: none;"></div>

    <!-- Reports Table -->
    <table class="table">
        <thead>
        <tr>
            <th>Report ID</th>
            <th>Message Content</th>
            <th>Reported By</th>
            <th>Reason</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody id="reports-container">
        <tr>
            <td colspan="5">Loading reports...</td>
        </tr>
        </tbody>
    </table>
</div>

<script>
    // Load all reports via API
    async function loadReports() {
        try {
            const response = await fetch('/admin/reports', {
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token') // Assuming JWT for authentication
                }
            });
            const result = await response.json();

            const reportsContainer = document.getElementById('reports-container');
            reportsContainer.innerHTML = '';

            if (response.ok) {
                if (result.data.length === 0) {
                    reportsContainer.innerHTML = '<tr><td colspan="5">No reports found.</td></tr>';
                    return;
                }

                // Render each report as a table row
                result.data.forEach(report => {
                    const row = document.createElement('tr');

                    row.innerHTML = `
                            <td>${report.id}</td>
                            <td>${report.message_content}</td>
                            <td>${report.reported_by || 'Anonymous'}</td>
                            <td>${report.reason}</td>
                            <td>
                                <button class="btn btn-dismiss" data-id="${report.id}">Dismiss</button>
                                <button class="btn btn-delete" data-id="${report.message_id}">Delete Message</button>
                            </td>
                        `;

                    reportsContainer.appendChild(row);
                });

                // Attach dismiss event listeners
                document.querySelectorAll('.btn-dismiss').forEach(button => {
                    button.addEventListener('click', async (e) => {
                        const reportId = e.target.dataset.id;
                        if (confirm('Are you sure you want to dismiss this report?')) {
                            await dismissReport(reportId);
                            loadReports(); // Reload reports after dismissal
                        }
                    });
                });

                // Attach delete event listeners
                document.querySelectorAll('.btn-delete').forEach(button => {
                    button.addEventListener('click', async (e) => {
                        const messageId = e.target.dataset.id;
                        if (confirm('Are you sure you want to delete this message?')) {
                            await deleteMessage(messageId);
                            loadReports(); // Reload reports after deletion
                        }
                    });
                });
            } else {
                reportsContainer.innerHTML = `<tr><td colspan="5">${result.message || 'Failed to load reports.'}</td></tr>`;
            }
        } catch (error) {
            console.error('Error loading reports:', error);
            const errorMessage = document.getElementById('error-message');
            errorMessage.textContent = 'Unable to load reports. Please try again later.';
            errorMessage.style.display = 'block';
        }
    }

    // Dismiss a report via API
    async function dismissReport(reportId) {
        try {
            const response = await fetch(`/admin/reports/dismiss/${reportId}`, {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token'), // Assuming JWT
                    'Content-Type': 'application/json'
                }
            });

            const result = await response.json();
            if (!response.ok) {
                alert(result.message || 'Failed to dismiss the report.');
            } else {
                alert('Report dismissed successfully.');
            }
        } catch (error) {
            console.error('Error dismissing report:', error);
            alert('Unable to dismiss the report. Please try again later.');
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

    // Load reports on page load
    loadReports();
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

    .btn-dismiss {
        background-color: #28a745;
    }

    .btn-dismiss:hover {
        background-color: #218838;
    }

    .btn-delete {
        background-color: #dc3545;
    }

    .btn-delete:hover {
        background-color: #a71d2a;
    }
</style>
</body>
</html>
