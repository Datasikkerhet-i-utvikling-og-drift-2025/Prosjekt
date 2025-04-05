<?php
namespace helpers;

use helpers\ApiResponse;
use JetBrains\PhpStorm\NoReturn;
use JsonException;

/**
 * Class ApiHelper
 * Utility methods for managing API responses and input handling.
 */
class ApiHelper
{

    /**
     * Sends a JSON-formatted API response.
     *
     * @param int $statusCode HTTP status code.
     * @param ApiResponse $response Response object.
     * @throws JsonException
     */
    #[NoReturn] public static function sendApiResponse(int $statusCode, ApiResponse $response): void
    {
        Logger::info("Sending API response. Status: $statusCode, Message: " . $response->message);
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
        Logger::error("Sending API error response. Status: $statusCode, Message: $message");
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
        Logger::debug("ApiHelper::getInput called with Content-Type: $contentType");

        if (str_starts_with($contentType, 'application/json')) {
            $rawInput = file_get_contents('php://input');
            Logger::debug("Raw JSON input: $rawInput");

            if (empty($rawInput)) {
                Logger::warning("Empty JSON input received.");
                throw new JsonException('Empty JSON input.');
            }

            return json_decode($rawInput, true, 512, JSON_THROW_ON_ERROR);
        }

        if (
            str_starts_with($contentType, 'multipart/form-data') ||
            str_starts_with($contentType, 'application/x-www-form-urlencoded')
        ) {
            Logger::info("Returning \$_POST from ApiHelper::getInput.");
            return $_POST;
        }

        Logger::error("Unsupported Content-Type: $contentType");
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
        Logger::debug("ApiHelper::getJsonInput raw: $raw");
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
        Logger::debug("ApiHelper::isApiRequest detected as " . ($isApi ? "true" : "false"));
        return $isApi;
    }


    /**
     * Ensures that the request method is POST, otherwise responds with 405.
     * @throws JsonException
     */
    public static function requirePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Logger::warning("Method not allowed: " . $_SERVER['REQUEST_METHOD']);
            self::sendError(405, 'Method Not Allowed. Use POST.');
        }
    }


    /**
     * Validates the API Token provided in the Authorization header.
     *
     * @throws JsonException
     */
    public static function requireApiToken(): void
    {
        $expectedToken = $_ENV['API_TOKEN'] ?? null;

        if (empty($expectedToken)) {
            Logger::critical("API token is not configured in environment.");
            self::sendError(500, 'Internal server error. API token not configured.');
        }

        $headers = getallheaders();
        Logger::debug("ApiHelper::requireApiToken header: " . json_encode($headers));

        if (!isset($headers['Authorization']) || !str_starts_with($headers['Authorization'], 'Bearer ')) {
            Logger::warning("Missing or malformed Authorization header.");
            self::sendError(401, 'Unauthorized. Missing or invalid token.');
        }

        $providedToken = trim(str_replace('Bearer', '', $headers['Authorization']));

        if ($providedToken !== $expectedToken) {
            Logger::warning("Invalid API token provided.");
            self::sendError(403, 'Forbidden. Invalid API token.');
        }

        Logger::info("API token validated successfully.");
    }

}
