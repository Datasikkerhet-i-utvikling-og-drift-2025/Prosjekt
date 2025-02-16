<?php

// Use strict types
declare(strict_types=1);

// Start execution timer for debugging performance
$startExecutionTime = microtime(true);

// Load required files
require_once __DIR__ . '/../src/config/api-routes.php'; // API routes
require_once __DIR__ . '/../src/helpers/ApiHelper.php'; // API helper
require_once __DIR__ . '/../src/helpers/AuthHelper.php'; // Authentication helper
require_once __DIR__ . '/../src/helpers/Logger.php'; // Logger
require_once __DIR__ . '/../src/services/DatabaseManager.php'; // Database service
require_once __DIR__ . '/../src/repositories/UserRepository.php'; // User repository

use helpers\ApiHelper;
use helpers\AuthHelper;
use helpers\Logger;
use service\DatabaseService;
use repositories\UserRepository;

// Initialize Logger
Logger::info('API entry point initialized.');

// Ensure routes are loaded only once
static $routes = null;
if ($routes === null) {
    try {
        $routes = require __DIR__ . '/../src/config/api-routes.php';

        if (!is_array($routes) || empty($routes)) {
            throw new RuntimeException('No routes configured.');
        }

        Logger::info('Routes loaded successfully.');
    } catch (Exception $e) {
        Logger::error('Failed to load routes: ' . $e->getMessage());
        ApiHelper::sendError(500, 'Internal Server Error. Failed to load routes.');
        exit;
    }
}

// Initialize database connection
$dbService = new DatabaseService();
$userRepo = new UserRepository($dbService);

// Get HTTP method and sanitized request URI
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = strtok($_SERVER['REQUEST_URI'], '?'); // Strip query parameters

Logger::info("Incoming request: $method $requestUri");

// Match route (supporting dynamic routes like `/users/{id}`)
$matchedRoute = null;
$pathParams = [];

foreach ($routes as [$routeMethod, $routeUri, $callback]) {
    // Convert `{id}` placeholders into regex patterns
    $routePattern = preg_replace('#\{([a-zA-Z0-9_]+)\}#', '([^/]+)', $routeUri);

    if ($method === $routeMethod && preg_match("#^$routePattern$#", $requestUri, $matches)) {
        array_shift($matches); // Remove full match
        $matchedRoute = $callback;
        $pathParams = $matches;
        break;
    }
}

// Handle matched route
if ($matchedRoute) {
    try {
        // Validate authorization, if applicable
        AuthHelper::validateAuthorizationHeader();

        // Log matched route
        Logger::info("Matched route: $method $requestUri. Executing callback.");

        // Execute callback with path parameters
        call_user_func_array($matchedRoute, $pathParams);
    } catch (Exception $e) {
        Logger::error('Error executing route callback: ' . $e->getMessage());
        ApiHelper::sendError(500, 'Internal Server Error. Please try again later.', [
            'details' => $e->getMessage(),
        ]);
    }
} else {
    // Handle unmatched route
    Logger::warning("Route not found: $method $requestUri");
    ApiHelper::sendError(404, 'Route not found.');
}

// Log execution time for performance monitoring
$executionTime = round((microtime(true) - $startExecutionTime) * 1000, 2);
Logger::info("API request handling completed in {$executionTime}ms.");
