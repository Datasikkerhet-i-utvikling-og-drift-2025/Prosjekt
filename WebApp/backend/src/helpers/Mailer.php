<?php
//FIXME Slett denne etterhvert.
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../helpers/Logger.php';


use helpers\Logger;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $mail;

    public function __construct() {
        try {
            $this->mail = new PHPMailer(true);
            $this->mail->SMTPDebug = 2; // Aktiver SMTP debugging
            $this->mail->Debugoutput = function($str, $level) {
                Logger::info("SMTP Debug: " . $str);
            };

            $this->mail->isSMTP();
            $this->mail->Host = getenv('SMTP_HOST');
            $this->mail->SMTPAuth = true;
            $this->mail->Username = getenv('SMTP_USERNAME');
            $this->mail->Password = getenv('SMTP_PASSWORD');
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port = getenv('SMTP_PORT');

            Logger::info("SMTP Configuration: " . json_encode([
                    'host' => getenv('SMTP_HOST'),
                    'username' => getenv('SMTP_USERNAME'),
                    'port' => getenv('SMTP_PORT')
                ], JSON_THROW_ON_ERROR));
        } catch (Exception $e) {
            Logger::error("Mailer initialization failed: " . $e->getMessage());
            throw $e;
        }
    }

    public function sendPasswordReset($email, $resetToken) {
        try {
            Logger::info("Attempting to send password reset email to: " . $email);

            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/reset-password?token=" . $resetToken;
            Logger::info("Reset link generated: " . $resetLink);

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
            Logger::info("Password reset email sent successfully to: " . $email);
            return true;
        } catch (Exception $e) {
            Logger::error("Failed to send password reset email: " . $e->getMessage());
            return false;
        }
    }
}
