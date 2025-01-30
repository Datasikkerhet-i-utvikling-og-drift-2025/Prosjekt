<?php

require_once __DIR__ . '/controllers/UserController.php';



$method = $_SERVER['REQUEST_METHOD'];
$route = $_GET['route'] ?? null; // Get the 'route' query parameter

$controller = new UserController();

if ($route === 'users' && $method === 'POST') {
    $controller->saveUser();
} elseif ($route === 'users' && $method === 'GET') {
    $controller->getAllUsers();
} elseif ($route === 'login' && $method === 'POST') {
    $loginController->loginUser();
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
}


