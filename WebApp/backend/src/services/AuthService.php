<?php

namespace services;

use DateMalformedStringException;
use Exception;
use finfo;
use helpers\AuthHelper;
use helpers\InputValidator;
use helpers\ApiResponse;
use helpers\GrayLogger;
use managers\JWTManager;
use models\Course;
use repositories\PasswordResetRepository;
use repositories\UserRepository;
use repositories\LecturerRepository;
use factories\UserFactory;
use models\Lecturer;
use RuntimeException;
use Random\RandomException;
use Throwable;
use services\MailerService;
use DateTime;

/**
 * Class AuthService
 * Handles authentication and registration logic.
 * Compatible with both web and mobile clients.
 */
class AuthService
{
    private UserRepository $userRepository;
    private LecturerRepository $lecturerRepository;
    private JWTManager $jwtManager;

    private MailerService $mailerService;
    private array $config;
    private PasswordResetRepository $passwordResetRepository;
    private GrayLogger $logger;


    /**
     * AuthService constructor.
     *
     * @param UserRepository $userRepository
     * @param LecturerRepository $lecturerRepository
     * @param JWTManager $jwtManager
     */
    public function __construct(UserRepository $userRepository, LecturerRepository $lecturerRepository, JWTManager $jwtManager)
    {
        $this->userRepository = $userRepository;
        $this->lecturerRepository = $lecturerRepository;
        $this->jwtManager = $jwtManager;
        $this->logger = GrayLogger::getInstance();
        //$this->mailerService = $mailerService;
        //$this->config = $config;
        //$this->passwordResetRepository = $passwordResetRepository;
    }

    /**
     * Registers a new user.
     *
     * @param array $userData
     * @return ApiResponse
     * @throws RandomException|DateMalformedStringException|Exception
     */
    public function register(array $userData): ApiResponse
    {
        $this->logger->info('Register method called', ['payload' => $userData]);

        $validation = InputValidator::validateRegistration($userData);
        $this->logger->debug('Validation result', $validation);

        if (!empty($validation['errors'])) {
            $this->logger->warning('Validation failed', ['errors' => $validation['errors']]);
            return new ApiResponse(false, 'Validation failed.', null, $validation['errors']);
        }

        $data = $validation['sanitized'];

        if ($this->userRepository->getUserByEmail($data['email'])) {
            $this->logger->warning('Email already registered', ['email' => $data['email']]);
            return new ApiResponse(false, 'Email already registered.');
        }

        $this->logger->info('Hashing password and processing image upload...');
        $data['password'] = AuthHelper::hashPassword($data['password']);
        $data['imagePath'] = $this->handleProfilePictureUpload();

        $user = UserFactory::createUser($data);
        $this->logger->debug('User object created', ['user' => $user->toArray()]);
        $this->logger->debug('Sanitized userData', [$data]);
        $this->logger->debug('User object before save', ['user' => $user->toArray()]);
        if (!$this->userRepository->createUser($user)) {
            $this->logger->error('Failed to save user to database.');
            return new ApiResponse(false, 'Registration failed.');
        }

        $this->logger->info('User saved to database', ['email' => $user->email]);

        if ($user->role->value === 'lecturer') {
            $this->logger->info('Creating course for lecturer...');
            try {
                $lecturer = $this->userRepository->getUserByEmail($data['email']);
                $data['lecturerId'] = $lecturer->id ?? null;
                $data['pinCode'] = $data['coursePin'];
                $course = new Course($data);

                if (!$this->lecturerRepository->createCourse($course)) {
                    $this->logger->error('Failed to create course.');
                    return new ApiResponse(false, 'Registration succeeded, but course creation failed.');
                }

                $this->logger->info('Course created successfully.', ['courseCode' => $data['courseCode']]);
            } catch (Throwable $e) {
                $this->logger->error('Exception during course creation.', ['exception' => $e->getMessage()]);
                return new ApiResponse(false, 'Course creation failed.', null, ['exception' => $e->getMessage()]);
            }
        }

        $token = $this->jwtManager->generateToken([
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->role->value
        ]);

        $userArray = $user->toArray();
        $userArray['token'] = $token;

        $this->logger->info('Registration successful', ['user' => $userArray]);
        return new ApiResponse(true, 'Registration successful.', $userArray);
    }

    /**
     * Authenticates a user and issues JWT.
     *
     * @param array $credentials
     * @return ApiResponse
     * @throws DateMalformedStringException|Exception
     */
    public function login(array $credentials): ApiResponse
    {
        $this->logger->info('Login attempt', ['email' => $credentials['email'] ?? null]);
        $this->logger->debug('Login debug', [
            'email' => $credentials['email'] ?? null,
            'password' => $credentials['password'] ?? null
        ]);

        if (empty($credentials['email']) || empty($credentials['password'])) {
            return new ApiResponse(false, 'Email and password required.');
        }

        $user = $this->userRepository->getUserByEmail($credentials['email']);
        $this->logger->debug('User loaded from DB', ['user' => $user->toArray() ?? []]);

        $this->logger->debug("Password verification check", [
            'enteredPassword' => $credentials['password'],
            'storedHash' => $user->password,
            'verifyResult' => AuthHelper::verifyPassword($credentials['password'], $user->password)
        ]);


        if (!$user || !AuthHelper::verifyPassword($credentials['password'], $user->password)) {
            $this->logger->debug('Verifying password', [
                'plain' => $credentials['password'],
                'hashedFromDb' => $user->password
            ]);
            $this->logger->warning('Invalid login credentials', ['email' => $credentials['email']]);
            return new ApiResponse(false, 'Invalid credentials.');
        }

        $token = $this->jwtManager->generateToken([
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->role->value
        ]);

        $userArray = $user->toArray();
        $userArray['token'] = $token;

        $this->logger->info('Login successful', ['user' => $userArray]);

        return new ApiResponse(true, 'Login successful.', $userArray);
    }

    /**
     * Handles secure upload of a profile picture.
     *
     * @return string|null
     * @throws RandomException
     */
    private function handleProfilePictureUpload(): ?string
    {
        if (!isset($_FILES['profilePicture']) || $_FILES['profilePicture']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $file = $_FILES['profilePicture'];
        $allowedTypes = ['image/jpeg', 'image/png'];
        $maxSize = 10 * 1024 * 1024;

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $allowedTypes, true)) {
            throw new RuntimeException('Invalid image format.');
        }

        if ($file['size'] > $maxSize) {
            throw new RuntimeException('Image exceeds maximum size.');
        }

        $ext = $mimeType === 'image/png' ? 'png' : 'jpg';
        $fileName = bin2hex(random_bytes(16)) . '.' . $ext;
        $uploadDir = __DIR__ . '/../../public/uploads/profile_pictures/';

        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $uploadDir));
        }

        $path = $uploadDir . $fileName;
        if (!move_uploaded_file($file['tmp_name'], $path)) {
            throw new RuntimeException('Failed to save uploaded file.');
        }

        return '/uploads/profile_pictures/' . $fileName;
    }

    /**
     * Handles the logic for initiating a password reset request.
     * Finds user, generates token, stores it, and sends reset email.
     *
     * @param array $input Expects ['email' => string]
     * @return ApiResponse
     */
    public function handlePasswordResetRequest(array $input): ApiResponse
    {
        $email = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);

        try {
            // 1. Finn bruker basert på e-post
            $user = $this->userRepository->getUserByEmail($email);

            // 2. Viktig: Returner suksess uansett for å unngå bruker-enumerering
            if (!$user) {
                // Logg gjerne internt at forespørsel kom for ukjent e-post, men ikke avslør det utad
                error_log("Password reset requested for non-existent email: " . $email);
                return new ApiResponse(true, $this->getGenericSuccessMessage());
            }

            // 3. Generer et sikkert token
            $token = bin2hex(random_bytes(32)); // 64 tegn hex-streng

            // 4. Beregn utløpstid
            $expiryHours = (int) ($this->config['PASSWORD_RESET_TOKEN_EXPIRY_HOURS'] ?? 1);
            $expiresAt = (new DateTime())->add(new DateInterval("PT{$expiryHours}H"));

            // 5. Lagre tokenet i databasen (knyttet til brukeren)
            // Slett gjerne gamle tokens for brukeren først
            $this->passwordResetRepository->deleteTokensForUser($user->id);
            $stored = $this->passwordResetRepository->storeToken($user->id, $token, $expiresAt);

            if (!$stored) {
                error_log("Failed to store password reset token for user ID: " . $user->id);
                return new ApiResponse(false, 'Server error: Could not save reset token.');
            }

            // 6. Bygg reset-lenken
            $resetLink = rtrim($this->config['APP_URL'] ?? '', '/') . '/password-reset?token=' . urlencode($token); // Tilpass URL

            // 7. Forbered og send e-post
            $subject = 'Password Reset Request for ' . ($this->config['APP_NAME'] ?? 'Your Application');
            $body = $this->buildPasswordResetEmailBody($user->fullName ?? 'User', $resetLink, $expiryHours); // Antatt bruker har 'name'

            $mailSent = $this->mailerService->sendMail(
                $email,
                $user->fullName ?? $email, // Mottaker navn
                $subject,
                $body, // HTML-body
                strip_tags($body) // Plain text alternativ
            // Konfigurasjon for avsender hentes trolig i MailerService basert på $this->config
            );

            if (!$mailSent) {
                error_log("Failed to send password reset email to: " . $email);
                // Teknisk sett kunne token blitt lagret, men e-post feilet.
                // Vurder om token skal slettes eller om bruker må prøve igjen.
                // Å returnere generell feil er tryggest.
                return new ApiResponse(false, 'Server error: Could not send reset email.');
            }

            // 8. Returner generell suksessmelding
            return new ApiResponse(true, $this->getGenericSuccessMessage());

        } catch (Exception $e) {
            error_log("Password Reset Request Exception: " . $e->getMessage());
            // Returner en generell feilmelding i produksjon
            return new ApiResponse(false, 'An unexpected error occurred. Please try again later.');
        }
    }

    /**
     * Handles the logic for resetting the password using a provided token.
     * Validates token, updates password if valid.
     *
     * @param array $input Expects ['token' => string, 'new_password' => string]
     * @return ApiResponse
     */
    public function handlePasswordReset(array $input): ApiResponse
    {
        $token = $input['token'] ?? null;
        $newPassword = $input['new_password'] ?? null;

        if (!$token || !$newPassword) {
            return new ApiResponse(false, 'Missing token or new password.');
        }

        try {
            // 1. Finn token-data i databasen
            $tokenData = $this->passwordResetRepository->findByToken($token);

            // 2. Valider token eksistens
            if (!$tokenData) {
                return new ApiResponse(false, 'Invalid password reset token.');
            }

            // 3. Valider token utløpsdato
            $expiresAt = $tokenData['expires_at']; // Antatt format som kan parses av DateTime
            if (!($expiresAt instanceof DateTime)) {
                $expiresAt = new DateTime($expiresAt); // Konverter hvis det er streng
            }

            if ($expiresAt < new DateTime()) {
                // Slett utløpt token for å rydde opp
                $this->passwordResetRepository->deleteToken($token);
                return new ApiResponse(false, 'Password reset token has expired.');
            }

            // 4. Valider nytt passord (legg til dine regler her)
            if (strlen($newPassword) < 8) { // Eksempel: Minimum lengde
                return new ApiResponse(false, 'New password must be at least 8 characters long.');
            }
            // Legg til flere regler (store/små bokstaver, tall, symboler) etter behov

            // 5. Hash det nye passordet
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            if ($hashedPassword === false) {
                error_log("Password hashing failed for user ID: " . $tokenData['user_id']);
                return new ApiResponse(false, 'Server error: Could not process new password.');
            }

            // 6. Oppdater brukerens passord i databasen
            $userId = $tokenData['user_id'];
            $updated = $this->userRepository->updatePassword($userId, $hashedPassword);

            if (!$updated) {
                error_log("Failed to update password for user ID: " . $userId);
                return new ApiResponse(false, 'Server error: Could not update password.');
            }

            // 7. Slett brukt token
            $this->passwordResetRepository->deleteToken($token);

            // 8. Send suksessrespons
            return new ApiResponse(true, 'Your password has been successfully reset.');

        } catch (Exception $e) {
            error_log("Password Reset Execution Exception: " . $e->getMessage());
            return new ApiResponse(false, 'An unexpected error occurred while resetting the password.');
        }
    }

    /**
     * Helper to get the generic success message for request stage.
     * @return string
     */
    private function getGenericSuccessMessage(): string
    {
        return 'If an account with that email address exists, instructions for resetting your password have been sent.';
    }

    /**
     * Helper to build the HTML email body.
     *
     * @param string $userName
     * @param string $resetLink
     * @param int $expiryHours
     * @return string
     */
    private function buildPasswordResetEmailBody(string $userName, string $resetLink, int $expiryHours): string
    {
        $appName = htmlspecialchars($this->config['APP_NAME'] ?? 'Our Application');
        $link = $resetLink; // Viktig å escape lenken

        // Bygg en enkel HTML-epost
        $body = "<p>Hello " . htmlspecialchars($userName) . ",</p>";
        $body .= "<p>You recently requested to reset your password for your {$appName} account. Click the link below to proceed:</p>";
        $body .= "<p><a href='{$link}'>Reset Your Password</a></p>";
        $body .= "<p>This password reset link is valid for {$expiryHours} hour(s).</p>";
        $body .= "<p>If you did not request a password reset, please ignore this email or contact support if you have concerns.</p>";
        $body .= "<p>Thank you,<br>The {$appName} Team</p>";

        return $body;
    }

}
