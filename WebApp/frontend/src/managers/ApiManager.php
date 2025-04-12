<?php

namespace managers;

use CURLFile;
use finfo;
use JsonException;
use Random\RandomException;
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
     * @var string The bearer key used for API authentication.
     */
    private string $apiKey;

    /**
     * @var string The base URL for the API, typically stored in an environment variable.
     */
    private string $baseUrl;

    /**
     * ApiManager constructor.
     *
     * @throws RuntimeException If either the base URL or the API key is not set.
     */
    public function __construct()
    {
        try {
            $this->apiKey = (string) getenv('API_KEY');
            if (empty($this->apiKey)) {
                throw new RuntimeException('API key (API_KEY) not set or empty in environment.');
            }

            $this->baseUrl = rtrim((string) getenv('API_URL'), '/');
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
            return $this->request($endpoint."?".http_build_query($queryParams), 'GET');
        }
        else {
            return $this->request($endpoint, 'GET');
        }
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

            $hasFile = isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK;

            $headers = [
                'Authorization: Bearer ' . $this->apiKey,
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
                    $profilePicturePath = $this->handleProfilePictureUpload();
                    if ($profilePicturePath) {
                        $data['image_path'] = $profilePicturePath;
                    }
                }

                // Handle the data (either with file or as JSON)
                $jsonData = json_encode($data, JSON_THROW_ON_ERROR);
                $headers[] = 'Content-Type: application/json';
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
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

    /**
     * Handles secure upload of a profile picture.
     *
     * @return string|null
     * @throws RandomException|RandomException
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



}
