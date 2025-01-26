<?php

// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Autoload required files
require_once __DIR__ . '/../src/config/app.php'; // Application config
require_once __DIR__ . '/../src/helpers/ApiHelper.php'; // API helpers
require_once __DIR__ . '/../src/config/Database.php'; // Database connection
require_once __DIR__ . '/../src/helpers/Logger.php'; // Logger for error tracking
require_once __DIR__ . '/../src/autoload.php';

// Ensure logs directory exists
if (!is_dir(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0777, true);
}

// Handle CORS (Cross-Origin Resource Sharing) headers
header('Access-Control-Allow-Origin: *');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    http_response_code(204); // No Content
    exit;
}

// Get HTTP method and requested URI
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = strtok($_SERVER['REQUEST_URI'], '?'); // Strip query parameters

// Log the request
Logger::info("Request received: $method $requestUri");

// Define view routes
$views = [
    '/' => __DIR__ . '/../src/views/auth/login.php',
    '/register' => __DIR__ . '/../src/views/auth/register.php',
    '/reset-password' => __DIR__ . '/../src/views/auth/reset-password.php',
    '/change-password' => __DIR__ . '/../src/views/auth/change-password.php',
    '/student/dashboard' => __DIR__ . '/../src/views/student/dashboard.php',
    '/student/send-message' => __DIR__ . '/../src/views/student/send-message.php',
    '/student/view-responses' => __DIR__ . '/../src/views/student/view-responses.php',
    '/lecturer/dashboard' => __DIR__ . '/../src/views/lecturer/dashboard.php',
    '/lecturer/read-messages' => __DIR__ . '/../src/views/lecturer/read-messages.php',
    '/lecturer/reply' => __DIR__ . '/../src/views/lecturer/reply.php',
    '/admin/dashboard' => __DIR__ . '/../src/views/admin/dashboard.php',
    '/admin/manage-users' => __DIR__ . '/../src/views/admin/manage-users.php',
    '/admin/manage-messages' => __DIR__ . '/../src/views/admin/manage-messages.php',
    '/admin/reports' => __DIR__ . '/../src/views/admin/reports.php',
    '/guest/view-messages' => __DIR__ . '/../src/views/guest/view-messages.php',
    '/guest/report-message' => __DIR__ . '/../src/views/guest/report-message.php',
];

// Handle view requests
if (isset($views[$requestUri])) {
    $viewPath = $views[$requestUri];
    if (file_exists($viewPath)) {
        require_once $viewPath;
        exit;
    } else {
        Logger::error("View not found: $requestUri");
        http_response_code(404);
        require_once __DIR__ . '/../public/errors/404.php';
        exit;
    }
}

// Load API routes
$routes = require_once __DIR__ . '/../src/config/api-routes.php';

// Validate API routes configuration
if (!is_array($routes) || empty($routes)) {
    Logger::error('No API routes configured.');
    http_response_code(500);
    ApiHelper::sendError(500, 'Internal Server Error: No routes configured.');
    exit;
}

// Match request to API route
$matchedRoute = null;
foreach ($routes as $route) {
    [$routeMethod, $routeUri, $callback] = $route;
    if ($method === $routeMethod && $requestUri === $routeUri) {
        $matchedRoute = $callback;
        break;
    }
}

// Handle API requests
if ($matchedRoute) {
    try {
        // Execute matched route callback
        Logger::info("Matched route: $method $requestUri");
        call_user_func($matchedRoute);
    } catch (Exception $e) {
        Logger::error('Error handling request: ' . $e->getMessage());
        http_response_code(500);
        ApiHelper::sendError(500, 'Internal Server Error', [
            'error' => $e->getMessage(),
        ]);
    }
} else {
    // Log unmatched routes
    Logger::error("Route not found: $method $requestUri");
    http_response_code(404);
    require_once __DIR__ . '/../public/errors/404.php';
}