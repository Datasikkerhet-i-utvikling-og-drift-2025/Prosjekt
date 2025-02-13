<?php

namespace helpers;

use Exception;
use JetBrains\PhpStorm\NoReturn;
use JsonException;
use Random\RandomException;
use RuntimeException;

class ApiHelper
{
    private static string $logDir = __DIR__ . '/../../logs';
    private static string $logFile = 'api.log'; // Log file name

    /**
     * Send a JSON response to the client.
     *
     * @param int $statusCode HTTP status code.
     * @param array $data The response data.
     * @param string $message An optional message.
     * @param bool $success Whether the request was successful.
     * @throws JsonException
     */
    #[NoReturn] public static function sendResponse(int $statusCode, array $data = [], string $message = '', bool $success = true): void
    {
        http_response_code($statusCode);

        // Log the response
        self::logApiActivity('response', [
            'status_code' => $statusCode,
            'message' => $message,
            'data' => $data,
            'success' => $success
        ]);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], JSON_THROW_ON_ERROR);
        exit;
    }

    /**
     * Send a JSON error response.
     *
     * @param int $statusCode HTTP status code.
     * @param string $message The error message.
     * @param array $errors Optional error details.
     * @throws JsonException
     */
    #[NoReturn] public static function sendError(int $statusCode, string $message, array $errors = []): void
    {
        // Log the error
        self::logApiActivity('error', [
            'status_code' => $statusCode,
            'message' => $message,
            'errors' => $errors
        ]);

        self::sendResponse($statusCode, $errors, $message, false);
    }

    /**
     * Validate required fields in a request.
     *
     * @param array $requiredFields The required fields.
     * @param array $requestData The request data.
     * @throws JsonException
     */
    public static function validateRequest(array $requiredFields, array $requestData): void
    {
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (empty($requestData[$field])) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            self::sendError(400, 'Missing required fields.', ['missing_fields' => $missingFields]);
        }
    }

    /**
     * Parse and return JSON input data.
     *
     * @return array The parsed JSON input.
     * @throws JsonException
     */
    public static function getJsonInput(): array
    {
        // Ensure content-type is application/json
        if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
            self::sendError(415, 'Unsupported Media Type. Please use application/json.');
        }

        // Read raw input data
        $input = file_get_contents('php://input');

        // Log raw input
        Logger::info("Received JSON input: " . var_export($input, true));

        // Decode JSON safely
        try {
            $data = json_decode($input, true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
            self::sendError(400, 'Invalid JSON format.');
        }

        return $data ?? [];
    }

    /**
     * Generate a secure UUID (Universally Unique Identifier).
     *
     * @return string The generated UUID.
     * @throws RandomException
     */
    public static function generateUuid(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Validate an email format.
     *
     * @param string $email The email to validate.
     * @throws JsonException
     */
    public static function validateEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            self::sendError(400, 'Invalid email format.');
        }
    }

    /**
     * Validate an API key (optional, for securing API access).
     *
     * @param string $apiKey The provided API key.
     * @param array $validApiKeys List of valid API keys.
     * @throws JsonException
     */
    public static function validateApiKey(string $apiKey, array $validApiKeys = []): void
    {
        if (!in_array($apiKey, $validApiKeys, true)) {
            self::sendError(401, 'Unauthorized: Invalid API key.');
        }
    }

    /**
     * Log API request/response activity.
     *
     * @param string $type The log type ('request', 'response', or 'error').
     * @param array $data The data to log.
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

    /**
     * Fetch data from an external API.
     *
     * @param string $url The API endpoint to fetch from.
     * @return array|null The decoded JSON response or null on failure.
     */
    public static function fetchDataFromAPI(string $url): ?array
    {
        $baseUrl = "http://localhost:8080";

        try {
            $response = file_get_contents($baseUrl . $url);
            if ($response === false) {
                Logger::error("Failed to fetch data from API: " . $url);
                return null;
            }
            return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
            Logger::error("Error fetching data from API: " . $url . " - " . $e->getMessage());
            return null;
        }
    }
}
