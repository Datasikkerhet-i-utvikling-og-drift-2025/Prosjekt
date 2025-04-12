<?php

// Strict mode and debug-friendly
declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);

error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING);
ini_set('display_errors', '0');


use helpers\ApiHelper;
use helpers\Logger;

// Load dependencies
require_once __DIR__ . '/src/autoload.php';
require_once __DIR__ . '/src/config/app.php';
require_once __DIR__ . '/src/helpers/ApiHelper.php';
require_once __DIR__ . '/src/helpers/Logger.php';

// Start logger
Logger::info("Backend index.php hit");

// Handle CORS preflight (optional)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    http_response_code(204);
    exit;
}

// Accept CORS for all other requests
header('Access-Control-Allow-Origin: *');

// Load routes
$routes = require __DIR__ . '/src/config/api-routes.php';
$requestUri = strtok($_SERVER['REQUEST_URI'], '?');
$method = $_SERVER['REQUEST_METHOD'];

// Match route
$matchedRoute = null;
$pathParams = [];

foreach ($routes as [$routeMethod, $routeUri, $callback]) {
    $pattern = preg_replace('#\{[^/]+\}#', '([^/]+)', $routeUri);
    if ($method === $routeMethod && preg_match("#^$pattern$#", $requestUri, $matches)) {
        array_shift($matches);
        $matchedRoute = $callback;
        $pathParams = $matches;
        break;
    }
}

if ($matchedRoute) {
    try {
        call_user_func_array($matchedRoute, $pathParams);
    } catch (Throwable $e) {
        Logger::error("API exception: " . $e->getMessage());
        ApiHelper::sendError(500, 'Internal error', ['exception' => $e->getMessage()]);
    }
} else {
    Logger::warning("No API route matched: $method $requestUri");
    ApiHelper::sendError(404, 'Route not found');
}
