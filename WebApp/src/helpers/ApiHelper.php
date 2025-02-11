<?php

namespace helpers;

require_once __DIR__ . '/../helpers/Logger.php';


class ApiHelper
{
    private static string $logDir = __DIR__ . '/../../logs';
    private static string $logFile = 'api.log'; // Log file name

    /**
     * Send a JSON response
     */
    public static function sendResponse($statusCode, $data = [], $message = '', $success = true)
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
     * Send a JSON error response
     */
    public static function sendError($statusCode, $message, $errors = [])
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
     * Validate required fields in a request
     */
    public static function validateRequest($requiredFields, $requestData)
    {
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (!isset($requestData[$field]) || empty($requestData[$field])) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            self::sendError(400, 'Missing required fields.', ['missing_fields' => $missingFields]);
        }
    }

    /**
     * Parse JSON input and return it as an array
     */
    public static function getJsonInput() {
        // Leser rådata fra php://input
        $input = file_get_contents('php://input');
        Logger::info("Input: " . var_export($input, true));
        
        // Initialiserer en tom array for å lagre dekodet data
        $data = [];
        
        // Dekoder URL-encoded data til en PHP-array
        parse_str($input, $data);
        // Returnerer dekodet data
        return $data;
    }


    /**
     * Generate a UUID (useful for anonymous IDs)
     */
    public static function generateUuid()
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Validate email format
     */
    public static function validateEmail($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            self::sendError(400, 'Invalid email format.');
        }
    }

    /**
     * Check for a valid API key (optional, if you're securing your API)
     */
    public static function validateApiKey($apiKey, $validApiKeys = [])
    {
        if (!in_array($apiKey, $validApiKeys)) {
            self::sendError(401, 'Invalid API key.');
        }
    }

    /**
     * Log API request/response to the log file
     */
    private static function logApiActivity($type, $data)
    {
        self::ensureLogDirectoryExists();

        $logFilePath = self::getLogFilePath();
        $logEntry = date('Y-m-d H:i:s') . " [$type] " . json_encode($data, JSON_PRETTY_PRINT) . PHP_EOL;

        try {
            file_put_contents($logFilePath, $logEntry, FILE_APPEND | LOCK_EX);
        } catch (Exception $e) {
            error_log("ApiHelper error: Unable to write to log file. " . $e->getMessage());
        }
    }

    /**
     * Ensure the log directory exists
     */
    private static function ensureLogDirectoryExists()
    {
        if (!is_dir(self::$logDir) && !mkdir($concurrentDirectory = self::$logDir, 0777, true) && !is_dir($concurrentDirectory)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }
    }

    /**
     * Get the full path to the log file
     */
    private static function getLogFilePath()
    {
        return self::$logDir . '/' . self::$logFile;
    }
}
