<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Message - Feedback System</title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Include your CSS -->
</head>
<body>
<div class="container">
    <h1>Report a Message</h1>

    <!-- Error Message Placeholder -->
    <div id="error-message" style="color: red; display: none;"></div>
    <div id="success-message" style="color: green; display: none;"></div>

    <!-- Report Message Form -->
    <form id="report-message-form" action="/guest/messages/report" method="POST">
        <input type="hidden" id="message_id" name="message_id" value="<?php echo htmlspecialchars($_GET['message_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />

        <div class="form-group">
            <label for="report_reason">Reason for Reporting</label>
            <textarea id="report_reason" name="report_reason" rows="4" placeholder="Explain why this message should be reported..." required></textarea>
        </div>

        <button type="submit">Submit Report</button>
    </form>
</div>

<script>
    // Handle form submission
    const form = document.getElementById('report-message-form');
    form.addEventListener('submit', async (e) => {
        e.preventDefault(); // Prevent default form submission

        const messageId = document.getElementById('message_id').value;
        const reportReason = document.getElementById('report_reason').value;

        try {
            const response = await fetch('/guest/messages/report', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ message_id: messageId, report_reason: reportReason }),
            });

            const result = await response.json();

            if (response.ok) {
                const successMessage = document.getElementById('success-message');
                successMessage.textContent = 'Report submitted successfully!';
                successMessage.style.display = 'block';

                // Clear the form
                form.reset();
            } else {
                const errorMessage = document.getElementById('error-message');
                errorMessage.textContent = result.message || 'An error occurred while submitting your report.';
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
