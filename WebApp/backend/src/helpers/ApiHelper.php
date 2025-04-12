<?php
namespace helpers;

use helpers\ApiResponse;
use helpers\GrayLogger;
use JetBrains\PhpStorm\NoReturn;
use JsonException;

/**
 * Class ApiHelper
 * Utility methods for managing API responses and input handling.
 */
class ApiHelper
{

    private static GrayLogger $logger;

    public static function initLogger(): void
    {
        self::$logger = GrayLogger::getInstance();
    }


    /**
     * Sends a JSON-formatted API response.
     *
     * @param int $statusCode HTTP status code.
     * @param ApiResponse $response Response object.
     * @throws JsonException
     */
    #[NoReturn] public static function sendApiResponse(int $statusCode, ApiResponse $response): void
    {
        self::$logger->info("Sending API response. Status: $statusCode, Message: " . $response->message);
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($response->toArray(), JSON_THROW_ON_ERROR);
        exit;
    }


    /**
     * Sends a formatted API error response.
     *
     * @param int $statusCode HTTP status code.
     * @param string $message Error message.
     * @param array $errors Optional detailed error array.
     * @throws JsonException
     */
    #[NoReturn] public static function sendError(int $statusCode, string $message, array $errors = []): void
    {
        self::$logger->error("Sending API error response. Status: $statusCode, Message: $message");
        self::sendApiResponse($statusCode, new ApiResponse(false, $message, null, $errors));
    }


    /**
     * Retrieves sanitized input data based on the request's Content-Type.
     *
     * This method handles both JSON payloads (`application/json`) and
     * traditional form submissions (`multipart/form-data` or `application/x-www-form-urlencoded`).
     *
     * For JSON, it parses the body using `json_decode()` and throws if the payload is invalid.
     * For form-data, it returns `$_POST` directly.
     *
     * @return array The parsed and sanitized input array.
     * @throws JsonException If JSON is invalid or decoding fails.
     */
    public static function getInput(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        self::$logger->debug("ApiHelper::getInput called with Content-Type: $contentType");

        if (str_starts_with($contentType, 'application/json')) {
            $rawInput = file_get_contents('php://input');
            self::$logger->debug("Raw JSON input: $rawInput");

            if (empty($rawInput)) {
                self::$logger->warning("Empty JSON input received.");
                throw new JsonException('Empty JSON input.');
            }

            return json_decode($rawInput, true, 512, JSON_THROW_ON_ERROR);
        }

        if (
            str_starts_with($contentType, 'multipart/form-data') ||
            str_starts_with($contentType, 'application/x-www-form-urlencoded')
        ) {
            self::$logger->info("Returning \$_POST from ApiHelper::getInput.");
            return $_POST;
        }

        self::$logger->error("Unsupported Content-Type: $contentType");
        throw new JsonException("Unsupported Content-Type: {$contentType}");
    }


    /**
     * Retrieves JSON input from the request body.
     *
     * @return array Parsed JSON as associative array.
     * @throws JsonException
     */
    public static function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        self::$logger->debug("ApiHelper::getJsonInput raw: $raw");
        return json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    }


    /**
     * Checks if the request is an API call based on headers.
     *
     * @return bool
     */
    public static function isApiRequest(): bool
    {
        $isApi = isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json');
        self::$logger->debug("ApiHelper::isApiRequest detected as " . ($isApi ? "true" : "false"));
        return $isApi;
    }


    /**
     * Ensures that the request method is POST, otherwise responds with 405.
     * @throws JsonException
     */
    public static function requirePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::$logger->warning("Method not allowed: " . $_SERVER['REQUEST_METHOD']);
            self::sendError(405, 'Method Not Allowed. Use POST.');
        }
    }

    /**
     * @return void
     * @throws JsonException
     */
    public static function requireGet(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            self::$logger->warning("Method not allowed: " . $_SERVER['REQUEST_METHOD']);
            self::sendError(405, 'Method Not Allowed. Use GET.');
        }
    }


    /**
     * Validates the API Token provided in the Authorization header.
     *
     * @throws JsonException
     */
    public static function requireApiKey(): void
    {
        $expectedKey = $_ENV['API_KEY'] ?? null;

        if (empty($expectedKey)) {
            self::$logger->critical("API key is not configured in environment.");
            self::sendError(500, 'Internal server error. API key not configured.');
        }

        $headers = getallheaders();
        self::$logger->debug("ApiHelper::requireApiKey header: " . json_encode($headers, JSON_THROW_ON_ERROR));

        if (!isset($headers['Authorization']) || !str_starts_with($headers['Authorization'], 'Bearer ')) {
            self::$logger->warning("Missing or malformed Authorization header.");
            self::sendError(401, 'Unauthorized. Missing or invalid key.');
        }

        $providedKey = trim(str_replace('Bearer', '', $headers['Authorization']));

        if ($providedKey !== $expectedKey) {
            self::$logger->warning("Invalid API key provided.");
            self::sendError(403, 'Forbidden. Invalid API key.');
        }

        self::$logger->info("API key validated successfully.");
    }

}
