<?php

namespace helpers;

use Exception;
use JetBrains\PhpStorm\NoReturn;
use JsonException;
use RuntimeException;

class ApiHelper
{
    private static string $logDir = __DIR__ . '/../../logs';
    private static string $logFile = 'api.log';
    private static string $jwtSecretKey;

    /**
     * Initializes environment variables for ApiHelper.
     */
    public static function init(): void
    {
        self::$jwtSecretKey = getenv('JWT_SECRET') ?: 'default-secret-key';
    }


    /**
     * Determines if the current request is an API request based on headers.
     *
     * @return bool True if request is API, false otherwise.
     */
    public static function isApiRequest(): bool
    {
        return isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json');
    }

    /**
     * Sends a structured API response.
     *
     * @param int $statusCode HTTP status code.
     * @param ApiResponse $response The structured API response.
     * @throws JsonException
     */
    #[NoReturn] public static function sendApiResponse(int $statusCode, ApiResponse $response): void
    {
        http_response_code($statusCode);

        self::logApiActivity($response->success ? 'response' : 'error', $response->toArray());

        header('Content-Type: application/json');
        echo json_encode($response->toArray(), JSON_THROW_ON_ERROR);
        exit;
    }

    /**
     * Send a successful JSON response.
     *
     * @param int $statusCode HTTP status code.
     * @param string $message Success message.
     * @param array|null $data The response data.
     * @throws JsonException
     */
    #[NoReturn] public static function sendSuccess(int $statusCode, string $message, ?array $data = null): void
    {
        self::sendApiResponse($statusCode, new ApiResponse(true, $message, $data));
    }

    /**
     * Send an error JSON response.
     *
     * @param int $statusCode HTTP status code.
     * @param string $message The error message.
     * @param array|null $errors Optional error details.
     * @throws JsonException
     */
    #[NoReturn] public static function sendError(int $statusCode, string $message, ?array $errors = null): void
    {
        self::sendApiResponse($statusCode, new ApiResponse(false, $message, null, $errors));
    }

    /**
     * Parses and returns JSON input data.
     *
     * @return array The parsed JSON input.
     * @throws JsonException
     */
    public static function getJsonInput(): array
    {
        if (!isset($_SERVER['CONTENT_TYPE']) || !str_contains($_SERVER['CONTENT_TYPE'], 'application/json')) {
            self::sendError(415, 'Unsupported Media Type. Please use application/json.');
        }

        $input = file_get_contents('php://input');

        try {
            return json_decode($input, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            self::sendError(400, 'Invalid JSON format.');
        }

        return [];
    }

    /**
     * Generate a JWT token.
     *
     * @param array $payload The payload data.
     * @param int $expiry Expiration time in seconds.
     * @return string The generated JWT token.
     * @throws JsonException
     */
    public static function generateJwtToken(array $payload, int $expiry = 3600): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $payload['exp'] = time() + $expiry;

        $base64Header = self::base64UrlEncode(json_encode($header, JSON_THROW_ON_ERROR));
        $base64Payload = self::base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR));

        $signature = hash_hmac('sha256', "$base64Header.$base64Payload", self::$jwtSecretKey, true);
        $base64Signature = self::base64UrlEncode($signature);

        return "$base64Header.$base64Payload.$base64Signature";
    }

    /**
     * Validate and decode a JWT token.
     *
     * @param string $token The JWT token to validate.
     * @return array|null The decoded payload or null if invalid.
     * @throws JsonException
     */
    public static function validateJwtToken(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$base64Header, $base64Payload, $base64Signature] = $parts;
        $payload = json_decode(self::base64UrlDecode($base64Payload), true, 512, JSON_THROW_ON_ERROR);

        if (!$payload || !isset($payload['exp']) || $payload['exp'] < time()) {
            return null; // Token expired
        }

        $expectedSignature = hash_hmac('sha256', "$base64Header.$base64Payload", self::$jwtSecretKey, true);
        if (!hash_equals(self::base64UrlEncode($expectedSignature), $base64Signature)) {
            return null; // Invalid signature
        }

        return $payload;
    }

    /**
     * Base64 URL-safe encoding.
     *
     * @param string $data The data to encode.
     * @return string The encoded string.
     */
    private static function base64UrlEncode(string $data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    /**
     * Base64 URL-safe decoding.
     *
     * @param string $data The data to decode.
     * @return string The decoded string.
     */
    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }

    /**
     * Validate API authorization (JWT or API Key).
     *
     * @throws JsonException
     */
    public static function validateAuthorization(): void
    {
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = trim($_SERVER['HTTP_AUTHORIZATION']);
            if (str_starts_with($authHeader, 'Bearer ')) {
                $token = substr($authHeader, 7);
                if (!self::validateJwtToken($token)) {
                    self::sendError(401, 'Invalid JWT Token.');
                }
                return;
            }
        }

        if (!isset($_SESSION['user_id'])) {
            self::sendError(403, 'Unauthorized: Please login.');
        }
    }

    /**
     * Logs API request and response activities.
     *
     * @param string $type Log type ('request', 'response', 'error').
     * @param array $data Data to log.
     * @throws JsonException
     */
    private static function logApiActivity(string $type, array $data): void
    {
        self::ensureLogDirectoryExists();

        $logFilePath = self::getLogFilePath();
        $logEntry = date('Y-m-d H:i:s') . " [$type] " . json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT) . PHP_EOL;

        try {
            file_put_contents($logFilePath, $logEntry, FILE_APPEND | LOCK_EX);
        } catch (Exception $e) {
            error_log("ApiHelper error: Unable to write to log file. " . $e->getMessage());
        }
    }

    /**
     * Ensure the log directory exists.
     */
    private static function ensureLogDirectoryExists(): void
    {
        if (!is_dir(self::$logDir) && !mkdir($concurrentDirectory = self::$logDir, 0777, true) && !is_dir($concurrentDirectory)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }
    }

    /**
     * Get the full path to the log file.
     *
     * @return string The log file path.
     */
    private static function getLogFilePath(): string
    {
        return self::$logDir . '/' . self::$logFile;
    }
}
