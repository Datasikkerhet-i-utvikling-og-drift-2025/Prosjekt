<?php

// Load required files
require_once '../src/config/api-routes.php'; // API routes
require_once '../src/helpers/ApiHelper.php'; // API helper
require_once '../src/helpers/AuthHelper.php'; // Authentication helper
require_once '../src/helpers/Logger.php'; // Logger

// Load routes
$routes = require '../src/config/api-routes.php';

// Check if $routes is valid
if (!is_array($routes) || empty($routes)) {
    Logger::error('Routes are not properly configured or empty.');
    http_response_code(500);
    ApiHelper::sendError(500, 'Internal Server Error. No routes configured.');
    exit;
}

// Get HTTP method and request URI
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = strtok($_SERVER['REQUEST_URI'], '?'); // Remove query parameters

// Match route
$matchedRoute = null;

foreach ($routes as $route) {
    [$routeMethod, $routeUri, $callback] = $route;

    if ($method === $routeMethod && $requestUri === $routeUri) {
        $matchedRoute = $callback;
        break;
    }
}

if ($matchedRoute) {
    try {
        // Authenticate if required
        AuthHelper::validateAuthorizationHeader();

        // Call the callback
        call_user_func($matchedRoute);
    } catch (Exception $e) {
        Logger::error('Error handling request: ' . $e->getMessage());
        http_response_code(500);
        ApiHelper::sendError(500, 'An internal server error occurred.', [
            'error' => $e->getMessage(),
        ]);
    }
} else {
    // Route not found
    http_response_code(404);
    ApiHelper::sendError(404, 'Route not found.');
}
