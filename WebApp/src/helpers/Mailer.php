<?php
require_once __DIR__ . '/../../vendor/autoload.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);
        
        // Server settings
        $this->mail->isSMTP();
        $this->mail->Host = getenv('SMTP_HOST');
        $this->mail->SMTPAuth = true;
        $this->mail->Username = getenv('SMTP_USERNAME');
        $this->mail->Password = getenv('SMTP_PASSWORD');
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = getenv('SMTP_PORT');
    }

    public function sendPasswordReset($email, $resetToken) {
        try {
            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/reset-password?token=" . $resetToken;
    
            $this->mail->setFrom(getenv('SMTP_USERNAME'), 'Feedback System');
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