<?php

// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use helpers\ApiHelper;
use helpers\Logger;
use managers\DatabaseManager;
use repositories\UserRepository;

// Autoload required files
require_once __DIR__ . '/../src/config/app.php'; // Application config
require_once __DIR__ . '/../src/helpers/ApiHelper.php'; // API helpers
require_once __DIR__ . '/../src/helpers/Logger.php'; // Logger for error tracking
require_once __DIR__ . '/../src/services/DatabaseManager.php'; // Database connection
require_once __DIR__ . '/../src/repositories/UserRepository.php'; // User repository
require_once __DIR__ . '/../src/autoload.php'; // Autoloader for PSR-4 compliance

// Ensure logs directory exists
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir) && !mkdir($logDir, 0777, true) && !is_dir($logDir)) {
    throw new RuntimeException(sprintf('Failed to create logs directory: "%s"', $logDir));
}

// Handle CORS (Cross-Origin Resource Sharing) headers
header('Access-Control-Allow-Origin: *');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    http_response_code(204); // No Content
    exit;
}

// Initialize database connection
$dbService = new DatabaseManager();
$userRepo = new UserRepository($dbService);

// Get HTTP method and requested URI
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = strtok($_SERVER['REQUEST_URI'], '?'); // Strip query parameters

// Log the request
Logger::info("Incoming Request: $method $requestUri");

// Load view routes
$views = require __DIR__ . '/../src/config/view-routes.php';

// Handle view requests
if (isset($views[$requestUri])) {
    $viewPath = $views[$requestUri];
    if (file_exists($viewPath)) {
        Logger::info("Serving view: $requestUri");
        require_once $viewPath;
    } else {
        Logger::error("View not found: $requestUri");
        http_response_code(404);
        require_once __DIR__ . '/../public/errors/404.php';
    }
    exit;
}

// Load API routes
$routes = require __DIR__ . '/../src/config/api-routes.php';

// Validate API routes configuration
if (!is_array($routes) || empty($routes)) {
    Logger::error('API routes configuration is empty.');
    ApiHelper::sendError(500, 'Internal Server Error: No API routes configured.');
    exit;
}

// Match request to API route
$matchedRoute = null;
foreach ($routes as $route) {
    [$routeMethod, $routeUri, $callback] = $route;
    if ($method === $routeMethod && preg_match("#^$routeUri$#", $requestUri)) {
        $matchedRoute = $callback;
        break;
    }
}

// Handle API requests
if ($matchedRoute) {
    try {
        Logger::info("Matched API route: $method $requestUri");
        call_user_func($matchedRoute);
    } catch (Exception $e) {
        Logger::error('API request handling failed: ' . $e->getMessage());
        ApiHelper::sendError(500, 'Internal Server Error', [
            'error' => $e->getMessage(),
        ]);
    }
} else {
    // Log unmatched routes
    Logger::error("No matching route found: $method $requestUri");
    http_response_code(404);
    require_once __DIR__ . '/../public/errors/404.php';
}
