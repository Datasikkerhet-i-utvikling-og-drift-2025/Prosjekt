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
        self::sendApiResponse($statusCode, new ApiResponse(false, $message, null, $errors));
    }


    /**
     * Retrieves JSON input from the request body.
     *
     * @return array Parsed JSON as associative array.
     * @throws JsonException
     */
    public static function getJsonInput(): array
    {
        return json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
    }


    /**
     * Checks if the request is an API call based on headers.
     *
     * @return bool
     */
    public static function isApiRequest(): bool
    {
        return isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json');
    }


    /**
     * Ensures that the request method is POST, otherwise responds with 405.
     * @throws JsonException
     */
    public static function requirePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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
            self::sendError(500, 'Internal server error. API token not configured.');
        }

        $headers = getallheaders();
        if (!isset($headers['Authorization']) || !str_starts_with($headers['Authorization'], 'Bearer ')) {
            self::sendError(401, 'Unauthorized. Missing or invalid token.');
        }

        $providedToken = trim(str_replace('Bearer', '', $headers['Authorization']));

        if ($providedToken !== $expectedToken) {
            self::sendError(403, 'Forbidden. Invalid API token.');
        }
    }

}
