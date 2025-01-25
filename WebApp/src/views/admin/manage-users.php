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
    <title>Manage Users - Feedback System</title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Include your CSS -->
</head>
<body>
<?php include '../src/views/partials/navbar.php'; ?> <!-- Include Navbar -->

<div class="container">
    <h1>Manage Users</h1>
    <p>Below is the list of all users in the system. You can edit or delete users as necessary.</p>

    <!-- Error Message Placeholder -->
    <div id="error-message" style="color: red; display: none;"></div>

    <!-- Users Table -->
    <table class="table">
        <thead>
        <tr>
            <th>User ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody id="users-container">
        <tr>
            <td colspan="5">Loading users...</td>
        </tr>
        </tbody>
    </table>
</div>

<script>
    // Load all users via API
    async function loadUsers() {
        try {
            const response = await fetch('/admin/users', {
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token') // Assuming JWT for authentication
                }
            });
            const result = await response.json();

            const usersContainer = document.getElementById('users-container');
            usersContainer.innerHTML = '';

            if (response.ok) {
                if (result.data.length === 0) {
                    usersContainer.innerHTML = '<tr><td colspan="5">No users found.</td></tr>';
                    return;
                }

                // Render each user as a table row
                result.data.forEach(user => {
                    const row = document.createElement('tr');

                    row.innerHTML = `
                            <td>${user.id}</td>
                            <td>${user.name}</td>
                            <td>${user.email}</td>
                            <td>${user.role}</td>
                            <td>
                                <a href="/admin/edit-user.php?user_id=${user.id}" class="btn btn-edit">Edit</a>
                                <button class="btn btn-delete" data-id="${user.id}">Delete</button>
                            </td>
                        `;

                    usersContainer.appendChild(row);
                });

                // Attach delete event listeners
                document.querySelectorAll('.btn-delete').forEach(button => {
                    button.addEventListener('click', async (e) => {
                        const userId = e.target.dataset.id;
                        if (confirm('Are you sure you want to delete this user?')) {
                            await deleteUser(userId);
                            loadUsers(); // Reload users after deletion
                        }
                    });
                });
            } else {
                usersContainer.innerHTML = `<tr><td colspan="5">${result.message || 'Failed to load users.'}</td></tr>`;
            }
        } catch (error) {
            console.error('Error loading users:', error);
            const errorMessage = document.getElementById('error-message');
            errorMessage.textContent = 'Unable to load users. Please try again later.';
            errorMessage.style.display = 'block';
        }
    }

    // Delete a user via API
    async function deleteUser(userId) {
        try {
            const response = await fetch(`/admin/users/delete/${userId}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token'), // Assuming JWT
                    'Content-Type': 'application/json'
                }
            });

            const result = await response.json();
            if (!response.ok) {
                alert(result.message || 'Failed to delete the user.');
            } else {
                alert('User deleted successfully.');
            }
        } catch (error) {
            console.error('Error deleting user:', error);
            alert('Unable to delete the user. Please try again later.');
        }
    }

    // Load users on page load
    loadUsers();
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

    .btn-edit {
        background-color: #ffc107;
    }

    .btn-edit:hover {
        background-color: #e0a800;
    }
</style>
</body>
</html>
