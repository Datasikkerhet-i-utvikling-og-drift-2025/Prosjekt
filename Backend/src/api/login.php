<?php

use api\controllers\LoginUser;

header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/controllers/LoginUser.php';

$method = $_SERVER['REQUEST_METHOD'];
$route = $_GET['route'] ?? null;

$controller = new LoginUser();

// // loggin in
if ($route === 'login' && $method === 'POST') {
    $controller->loginUser();
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
}

// // Check if the request method is POST
// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     $controller = new LoginUser();
//     $controller->loginUser();
// } else {
//     http_response_code(405);
//     echo json_encode(['error' => 'Method not allowed']);
// }