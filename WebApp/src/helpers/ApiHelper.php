<?php

class ApiHelper
{
    // Send a JSON response
    public static function sendResponse($statusCode, $data = [], $message = '', $success = true)
    {
        http_response_code($statusCode);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }

    // Send a JSON error response
    public static function sendError($statusCode, $message, $errors = [])
    {
        self::sendResponse($statusCode, $errors, $message, false);
    }

    // Validate required fields in a request
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

    // Parse JSON input and return it as an array
    public static function getJsonInput()
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            self::sendError(400, 'Invalid JSON input.', ['error' => json_last_error_msg()]);
        }

        return $data;
    }

    // Generate a UUID (useful for anonymous IDs)
    public static function generateUuid()
    {
        return bin2hex(random_bytes(16));
    }

    // Check for a valid API key (optional, if you're securing your API)
    public static function validateApiKey($apiKey, $validApiKeys = [])
    {
        if (!in_array($apiKey, $validApiKeys)) {
            self::sendError(401, 'Invalid API key.');
        }
    }
}
