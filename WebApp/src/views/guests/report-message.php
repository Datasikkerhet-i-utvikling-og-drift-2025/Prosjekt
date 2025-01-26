<?php include '../src/views/partials/header.php'; ?>

<div class="container">
    <h1>Report a Message</h1>

    <!-- Error or Success Message -->
    <?php if (!empty($_GET['error'])): ?>
        <div id="error-message" style="color: red;">
            <?= htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php elseif (!empty($_GET['success'])): ?>
        <div id="success-message" style="color: green;">
            <?= htmlspecialchars($_GET['success'], ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <!-- Report Message Form -->
    <form action="/guest/messages/report" method="POST">
        <input type="hidden" name="message_id" value="<?= htmlspecialchars($_GET['message_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />

        <div class="form-group">
            <label for="report_reason">Reason for Reporting</label>
            <textarea id="report_reason" name="report_reason" rows="4" placeholder="Explain why this message should be reported..." required></textarea>
        </div>

        <button type="submit">Submit Report</button>
    </form>
</div>

<?php include '../src/views/partials/footer.php'; ?>
