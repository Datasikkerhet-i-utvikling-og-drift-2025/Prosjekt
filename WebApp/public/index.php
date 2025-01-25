<?php

// Enable error reporting in development mode
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Load dependencies
require_once '../src/config/app.php'; // Application config
require_once '../src/helpers/ApiHelper.php'; // API helpers
require_once '../src/config/database.php'; // Database connection
require_once '../src/helpers/Logger.php'; // Logger for error tracking

// Load application config
$appConfig = require_once '../src/config/app.php';

// Set CORS headers for cross-origin requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    exit(0);
} else {
    header('Access-Control-Allow-Origin: *');
}

// Get the HTTP method and request URI
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = strtok($_SERVER['REQUEST_URI'], '?'); // Strip query parameters

// Routing for views (handling static pages)
$views = [
    '/' => '../src/views/auth/login.php',
    '/register' => '../src/views/auth/register.php',
    '/reset-password' => '../src/views/auth/reset-password.php',
    '/change-password' => '../src/views/auth/change-password.php',
    '/student/dashboard' => '../src/views/student/dashboard.php',
    '/student/send-message' => '../src/views/student/send-message.php',
    '/student/view-responses' => '../src/views/student/view-responses.php',
    '/lecturer/dashboard' => '../src/views/lecturer/dashboard.php',
    '/lecturer/read-messages' => '../src/views/lecturer/read-messages.php',
    '/lecturer/reply' => '../src/views/lecturer/reply.php',
    '/admin/dashboard' => '../src/views/admin/dashboard.php',
    '/admin/manage-users' => '../src/views/admin/manage-users.php',
    '/admin/manage-messages' => '../src/views/admin/manage-messages.php',
    '/admin/reports' => '../src/views/admin/reports.php',
    '/guest/view-messages' => '../src/views/guest/view-messages.php',
    '/guest/report-message' => '../src/views/guest/report-message.php',
];

// Handle view requests
if (array_key_exists($requestUri, $views)) {
    if (file_exists($views[$requestUri])) {
        require_once $views[$requestUri];
        exit;
    } else {
        Logger::error("View not found: $requestUri");
        http_response_code(404);
        require_once '../public/errors/404.php';
        exit;
    }
}

// Load API routes
$routes = require_once '../src/config/api-routes.php';

// Debugging - Check if routes are valid
if (!is_array($routes) || empty($routes)) {
    $message = 'Routes are not properly configured or empty.';
    Logger::error($message);

    http_response_code(500);
    ApiHelper::sendError(500, 'Internal Server Error. No routes configured.');
    exit;
}

// Match the request to an API route
$matchedRoute = null;
if (is_array($routes)) {
    foreach ($routes as $route) {
        [$routeMethod, $routeUri, $callback] = $route;

        if ($method === $routeMethod && $requestUri === $routeUri) {
            $matchedRoute = $callback;
            break;
        }
    }
}

// Handle the matched API route
if ($matchedRoute) {
    try {
        // Call the corresponding controller method
        call_user_func($matchedRoute);
    } catch (Exception $e) {
        // Log and send unexpected errors
        Logger::error('Error handling request: ' . $e->getMessage());

        http_response_code(500);
        ApiHelper::sendError(500, 'An internal server error occurred.', [
            'error' => $e->getMessage(),
        ]);
    }
} else {
    // Log unmatched route
    $message = "Route not found: $method $requestUri";
    Logger::error($message);

    // If no route matches, return a 404 error
    http_response_code(404);
    require_once '../public/errors/404.php';
}
