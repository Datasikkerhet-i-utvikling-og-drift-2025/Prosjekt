<?php

namespace managers;

use JsonException;
use RuntimeException;
use Throwable;

/**
 * Class ApiManager
 *
 * Manages HTTP requests (GET, POST, PUT, DELETE) to a specified API endpoint.
 * Provides error handling and returns decoded JSON responses.
 *
 * @package managers
 */
class ApiManager
{
    /**
     * @var string The bearer token used for API authentication.
     */
    private string $apiToken;

    /**
     * @var string The base URL for the API, typically stored in an environment variable.
     */
    private string $baseUrl;

    /**
     * ApiManager constructor.
     *
     * @throws RuntimeException If either the base URL or the API token is not set.
     */
    public function __construct()
    {
        try {
            $this->apiToken = (string) getenv('API_TOKEN');
            if (empty($this->apiToken)) {
                throw new RuntimeException('API token (API_TOKEN) not set or empty in environment.');
            }

            $this->baseUrl = rtrim((string) getenv('URL'), '/');
            if (empty($this->baseUrl)) {
                throw new RuntimeException('Base URL (URL) not set in environment.');
            }
        } catch (RuntimeException $e) {
            throw new RuntimeException('Error constructing ApiManager: ' . $e->getMessage());
        }
    }

    /**
     * Sends a GET request to the given endpoint.
     *
     * @param string $endpoint    The API endpoint (relative to the base URL).
     * @param array  $queryParams Optional query parameters to append to the request URL.
     *
     * @return array Returns an associative array with 'success', 'data' (if successful), or 'errors'.
     */
    public function get(string $endpoint, array $queryParams = []): array
    {
        if (!empty($queryParams)) {
            $endpoint .= '?' . http_build_query($queryParams);
        }

        return $this->request($endpoint, 'GET');
    }

    /**
     * Sends a POST request to the given endpoint.
     *
     * @param string $endpoint The API endpoint (relative to the base URL).
     * @param array  $data     Data to include in the POST request body.
     *
     * @return array Returns an associative array with 'success', 'data' (if successful), or 'errors'.
     */
    public function post(string $endpoint, array $data = []): array
    {
        return $this->request($endpoint, 'POST', $data);
    }

    /**
     * Sends a PUT request to the given endpoint.
     *
     * @param string $endpoint The API endpoint (relative to the base URL).
     * @param array  $data     Data to include in the PUT request body.
     *
     * @return array Returns an associative array with 'success', 'data' (if successful), or 'errors'.
     */
    public function put(string $endpoint, array $data = []): array
    {
        return $this->request($endpoint, 'PUT', $data);
    }

    /**
     * Sends a DELETE request to the given endpoint.
     *
     * @param string $endpoint The API endpoint (relative to the base URL).
     * @param array  $data     Optional data to include in the request body (depending on the API).
     *
     * @return array Returns an associative array with 'success', 'data' (if successful), or 'errors'.
     */
    public function delete(string $endpoint, array $data = []): array
    {
        return $this->request($endpoint, 'DELETE', $data);
    }

    /**
     * Internal helper method to perform the cURL request.
     *
     * @param string $endpoint The API endpoint (relative to the base URL).
     * @param string $method   The HTTP method (GET, POST, PUT, DELETE).
     * @param array  $data     The data to include in the request body for POST, PUT, DELETE.
     *
     * @return array Returns an associative array with 'success', 'httpCode', 'data' (if successful), or 'errors'.
     */
    private function request(string $endpoint, string $method, array $data = []): array
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');

        try {
            $ch = curl_init();
            if ($ch === false) {
                throw new RuntimeException('Failed to initialize cURL.');
            }

            $hasFile = isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] === UPLOAD_ERR_OK;

            $headers = [
                'Authorization: Bearer ' . $this->apiToken,
                'Accept: application/json'
            ];

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            if (!empty($data) && $method !== 'GET') {
                if ($hasFile) {

                    $postFields = array_map(static function ($value) {
                        return $value;
                    }, $data);

                    $postFields['profilePicture'] = new \CURLFile(
                        $_FILES['profilePicture']['tmp_name'],
                        $_FILES['profilePicture']['type'],
                        $_FILES['profilePicture']['name']
                    );

                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
                } else {
                    $jsonData = json_encode($data, JSON_THROW_ON_ERROR);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
                    $headers[] = 'Content-Type: application/json';
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // Update with Content-Type
                }
            }

            $response = curl_exec($ch);
            $error = curl_error($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($error) {
                return [
                    'success' => false,
                    'httpCode' => $httpCode,
                    'errors' => ['Curl error: ' . $error]
                ];
            }

            $decodedResponse = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

            if ($httpCode < 200 || $httpCode >= 300) {
                return [
                    'success' => false,
                    'httpCode' => $httpCode,
                    'errors' => [
                        'API responded with HTTP code ' . $httpCode,
                        'Response: ' . var_export($decodedResponse, true)
                    ]
                ];
            }

            return [
                'success' => true,
                'httpCode' => $httpCode,
                'data' => $decodedResponse
            ];
        } catch (JsonException $e) {
            return [
                'success' => false,
                'httpCode' => 500,
                'errors' => ['Invalid JSON handling: ' . $e->getMessage()]
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'httpCode' => 500,
                'errors' => ['Runtime error: ' . $e->getMessage()]
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'httpCode' => 500,
                'errors' => ['Unexpected error: ' . $e->getMessage()]
            ];
        }
    }

}
