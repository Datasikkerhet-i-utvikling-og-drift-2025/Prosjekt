<?php

// Use strict types
declare(strict_types=1);

// Load required files
require_once __DIR__ . '/../src/config/api-routes.php'; // API routes
require_once __DIR__ . '/../src/helpers/ApiHelper.php'; // API helper
require_once __DIR__ . '/../src/helpers/AuthHelper.php'; // Authentication helper
require_once __DIR__ . '/../src/helpers/Logger.php'; // Logger

// Initialize Logger
Logger::info('API entry point initialized.');

// Load routes
try {
    $routes = require __DIR__ . '/../src/config/api-routes.php';

    if (!is_array($routes) || empty($routes)) {
        Logger::error('No routes configured or routes array is invalid.');
        http_response_code(500);
        ApiHelper::sendError(500, 'Internal Server Error. No routes configured.');
        exit;
    }

    Logger::info('Routes loaded successfully.');
} catch (Exception $e) {
    Logger::error('Failed to load routes: ' . $e->getMessage());
    http_response_code(500);
    ApiHelper::sendError(500, 'Internal Server Error. Failed to load routes.');
    exit;
}

// Get HTTP method and sanitized request URI
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = strtok($_SERVER['REQUEST_URI'], '?'); // Strip query parameters

Logger::info("Incoming request: $method $requestUri");

// Match route
$matchedRoute = null;

foreach ($routes as $route) {
    [$routeMethod, $routeUri, $callback] = $route;

    if ($method === $routeMethod && $requestUri === $routeUri) {
        $matchedRoute = $callback;
        break;
    }
}

// Handle matched route
if ($matchedRoute) {
    try {
        // Validate authorization, if applicable
        AuthHelper::validateAuthorizationHeader();

        // Execute the matched route's callback
        Logger::info("Matched route: $method $requestUri. Executing callback.");
        call_user_func($matchedRoute);
    } catch (Exception $e) {
        Logger::error('Error executing route callback: ' . $e->getMessage());
        http_response_code(500);
        ApiHelper::sendError(500, 'Internal Server Error. Please try again later.', [
            'details' => $e->getMessage(),
        ]);
    }
} else {
    // Handle unmatched route
    Logger::warning("Route not found: $method $requestUri");
    http_response_code(404);
    ApiHelper::sendError(404, 'Route not found.');
}

// Log end of execution
Logger::info('API request handling completed.');
