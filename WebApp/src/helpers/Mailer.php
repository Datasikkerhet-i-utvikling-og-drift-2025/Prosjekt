<?php

require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    private $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);

        // Server settings
        $this->mailer->isSMTP();
        $this->mailer->Host = getenv('MAIL_HOST') ?: 'smtp.example.com';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = getenv('MAIL_USERNAME') ?: 'your_email@example.com';
        $this->mailer->Password = getenv('MAIL_PASSWORD') ?: 'your_password';
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = getenv('MAIL_PORT') ?: 587;

        // Sender info
        $this->mailer->setFrom(
            getenv('MAIL_FROM_ADDRESS') ?: 'noreply@example.com',
            getenv('MAIL_FROM_NAME') ?: 'Your App Name'
        );
    }

    // Send an email
    public function sendEmail($to, $subject, $body, $altBody = '')
    {
        try {
            // Validate email
            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email address: $to");
            }

            // Recipient
            $this->mailer->addAddress($to);

            // Email content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->AltBody = $altBody;

            // Send email
            $this->mailer->send();
            return ['success' => true];
        } catch (Exception $e) {
            $errorMessage = "Email could not be sent to $to. Error: " . $this->mailer->ErrorInfo;
            Logger::error($errorMessage);
            return ['success' => false, 'error' => $errorMessage];
        }
    }

    // Send a password reset email
    public function sendPasswordReset($to, $resetLink)
    {
        $subject = 'Password Reset Request';
        $body = "
            <p>You requested a password reset. Click the link below to reset your password:</p>
            <p><a href='$resetLink'>$resetLink</a></p>
            <p>If you didn't request this, please ignore this email.</p>
        ";
        $altBody = "You requested a password reset. Visit this link to reset your password: $resetLink";

        return $this->sendEmail($to, $subject, $body, $altBody);
    }

    // Send a user confirmation email
    public function sendUserConfirmation($to, $confirmationLink)
    {
        $subject = 'Confirm Your Account';
        $body = "
            <p>Thank you for signing up. Please confirm your account by clicking the link below:</p>
            <p><a href='$confirmationLink'>$confirmationLink</a></p>
        ";
        $altBody = "Thank you for signing up. Confirm your account here: $confirmationLink";

        return $this->sendEmail($to, $subject, $body, $altBody);
    }
}
