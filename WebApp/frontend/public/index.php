<?php
// Enable error reporting in development
ini_set('display_errors', '1');
error_reporting(E_ALL);

use managers\ApiManager;

// Autoload ApiManager
require_once __DIR__ . '/../src/managers/AccessControlManager.php';
require_once __DIR__ . '/../src/managers/ApiManager.php';
require_once __DIR__ . '/../src/managers/SessionManager.php';

// Load view routes
$routes = require __DIR__ . '/../config/view-routes.php';

// Sanitize URI
$requestUri = strtok($_SERVER['REQUEST_URI'], '?');
$method = $_SERVER['REQUEST_METHOD'];

// Route matching
if (isset($routes[$requestUri])) {
    $viewPath = $routes[$requestUri];

    if (file_exists($viewPath)) {
        require_once $viewPath;
    } else {
        http_response_code(404);
        require_once __DIR__ . '/errors/404.php';
    }

    exit;
}

// If no route matched and it's an API call, proxy it to backend
if (str_starts_with($requestUri, '/api/')) {
    try {
        $apiManager = new ApiManager();
        $input = $_POST;

        $response = match ($method) {
            'GET' => $apiManager->get($requestUri),
            'POST' => $apiManager->post($requestUri, $input),
            'PUT' => $apiManager->put($requestUri, $input),
            'DELETE' => $apiManager->delete($requestUri, $input),
            default => ['success' => false, 'errors' => ['Method not allowed']]
        };

        header('Content-Type: application/json');
        echo json_encode($response, JSON_THROW_ON_ERROR);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => [$e->getMessage()]], JSON_THROW_ON_ERROR);
    }

    exit;
}

// Fallback
http_response_code(404);
require_once __DIR__ . '/errors/404.php';
