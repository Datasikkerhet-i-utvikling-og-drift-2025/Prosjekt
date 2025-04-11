<?php

namespace services; // Eller ditt foretrukne namespace
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../helpers/Logger.php';



use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use helpers\Logger; // Antatt Logger-klasse
use Exception; // Standard PHP Exception

class MailerService
{
    private PHPMailer $mail;
    private string $fromAddress;
    private string $fromName;

    /**
     * MailerService constructor.
     * Initializes and configures PHPMailer for SMTP.
     * Retrieves configuration from environment variables.
     *
     * @throws Exception If initialization fails.
     * @throws PHPMailerException If PHPMailer configuration fails.
     */
    public function __construct()
    {
        try {
            // Hent avsenderinfo fra environment først
            $this->fromAddress = (string)getenv('MAIL_FROM_ADDRESS');
            $this->fromName = (string)getenv('MAIL_FROM_NAME');
            if (empty($this->fromAddress) || empty($this->fromName)) {
                throw new Exception('MAIL_FROM_ADDRESS and MAIL_FROM_NAME environment variables must be set.');
            }


            $this->mail = new PHPMailer(true); // true aktiverer exceptions

            // --- SMTP Debugging (Bør styres av miljøvariabel, f.eks. APP_DEBUG) ---
            $debugLevel = (getenv('APP_ENV') === 'development') ? 2 : 0; // 2 for dev, 0 for prod
            if ($debugLevel > 0) {
                $this->mail->SMTPDebug = $debugLevel;
                $this->mail->Debugoutput = function ($str, $level) {
                    // Bruk din logger
                    Logger::info("SMTP Level $level Debug: " . $str);
                };
            } else {
                $this->mail->SMTPDebug = 0; // Ingen debug output i prod
            }
            // --- Slutt på Debugging ---


            $this->mail->isSMTP();
            $this->mail->Host = (string)getenv('SMTP_HOST');
            $this->mail->SMTPAuth = true;
            $this->mail->Username = (string)getenv('SMTP_USERNAME');
            $this->mail->Password = (string)getenv('SMTP_PASSWORD');
            // Bruk konstant for kryptering hvis mulig, ellers streng
            $this->mail->SMTPSecure = getenv('SMTP_ENCRYPTION') === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS; // Default til SMTPS hvis ikke 'tls'
            $this->mail->Port = (int)getenv('SMTP_PORT'); // Port bør være et tall

            $this->mail->CharSet = 'UTF-8'; // Anbefalt for norske tegn

            // Logg konfigurasjonen (uten passord)
            Logger::info("SMTP Configuration Initialized: " . json_encode([
                    'host' => $this->mail->Host,
                    'username' => $this->mail->Username,
                    'port' => $this->mail->Port,
                    'secure' => $this->mail->SMTPSecure,
                    'from_address' => $this->fromAddress,
                    'from_name' => $this->fromName,
                    'debug_level' => $this->mail->SMTPDebug
                ], JSON_THROW_ON_ERROR));

        } catch (PHPMailerException $e) {
            Logger::error("PHPMailer configuration failed: " . $e->errorMessage()); // PHPMailer egen feilmelding
            throw $e; // Re-throw for å signalisere feil
        } catch (Exception $e) {
            Logger::error("Mailer initialization failed: " . $e->getMessage());
            throw $e; // Re-throw for å signalisere feil
        }
    }

    /**
     * Sends an email.
     *
     * @param string $toAddress Recipient email address.
     * @param string $toName Recipient name.
     * @param string $subject Email subject.
     * @param string $htmlBody HTML content of the email.
     * @param string|null $plainBody Optional plain text version of the email.
     * @return bool True if email was sent successfully, false otherwise.
     */
    public function sendMail(string $toAddress, string $toName, string $subject, string $htmlBody, ?string $plainBody = null): bool
    {
        try {
            // Set Fra (kan settes én gang, men trygt å sette her også)
            $this->mail->setFrom($this->fromAddress, $this->fromName);

            // Tøm tidligere mottakere/vedlegg (viktig hvis objektet gjenbrukes)
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            $this->mail->clearReplyTos();
            $this->mail->clearAllRecipients(); // Vær sikker

            // Legg til mottaker
            $this->mail->addAddress($toAddress, $toName);

            // Sett innhold
            $this->mail->isHTML(true); // Sett e-postformat til HTML
            $this->mail->Subject = $subject;
            $this->mail->Body = $htmlBody;
            // Lag AltBody automatisk fra HTML hvis ikke gitt, eller bruk den gitte
            $this->mail->AltBody = $plainBody ?? strip_tags($htmlBody);


            // Send e-posten
            $this->mail->send();
            Logger::info("Email sent successfully to: " . $toAddress . " with subject: " . $subject);
            return true;

        } catch (PHPMailerException $e) {
            // Log spesifikk PHPMailer-feil
            Logger::error("Mailer Error: " . $e->errorMessage() . " | To: {$toAddress}, Subject: {$subject}");
            return false;
        } catch (Exception $e) {
            // Log generell feil under sending
            Logger::error("General Error sending email: " . $e->getMessage() . " | To: {$toAddress}, Subject: {$subject}");
            return false;
        }
    }
}