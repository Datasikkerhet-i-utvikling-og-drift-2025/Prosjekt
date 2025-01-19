<?php
/**
 * Main API Entry Point
 *
 * Example usage:
 *  GET /api/index.php?route=users        -> Get all users
 *  POST /api/index.php?route=users       -> Create a new user
 */

header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/controllers/UserController.php';

// Determine the requested route (basic routing approach)
$route = isset($_GET['route']) ? $_GET['route'] : '';

// We create a simple dispatcher based on ?route=...
switch ($route) {
    case 'users':
        $controller = new UsersController();
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            $controller->getAllUsers();
        } elseif ($method === 'POST') {
            $controller->createUser();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;

    default:
        // Default fallback if no proper route is given
        http_response_code(404);
        echo json_encode(['error' => 'Invalid route']);
        break;
}
