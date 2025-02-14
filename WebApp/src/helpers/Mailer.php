<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);
        
        // Server settings
        $this->mail->isSMTP();
        $this->mail->Host = 'smtp.gmail.com'; // Eller din SMTP server
        $this->mail->SMTPAuth = true;
        $this->mail->Username = 'feedback.system.hiof@gmail.com';
        $this->mail->Password = 'ztfu pjoy bxds mtac'; // Bruk app-passord for Gmail
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = 587;
    }

    public function sendPasswordReset($email, $resetToken) {
        try {
            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/reset-password?token=" . $resetToken;

            $this->mail->setFrom('your-email@gmail.com', 'Your System');
            $this->mail->addAddress($email);
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Password Reset Request';
            $this->mail->Body = "
                <h2>Password Reset Request</h2>
                <p>Click the link below to reset your password:</p>
                <p><a href='$resetLink'>Reset Password</a></p>
                <p>If you didn't request this, please ignore this email.</p>
            ";

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            Logger::error("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
}