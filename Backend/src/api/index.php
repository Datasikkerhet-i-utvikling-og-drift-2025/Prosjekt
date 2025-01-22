<?php

require_once __DIR__ . '/controllers/UserController.php';

use api\controllers\UserController;

$method = $_SERVER['REQUEST_METHOD'];
$route = $_GET['route'] ?? null; // Get the 'route' query parameter

$controller = new UserController();

if ($route === 'users' && $method === 'POST') {
    $controller->saveUser();
} elseif ($route === 'users' && $method === 'GET') {
    $controller->getAllUsers();
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
}
