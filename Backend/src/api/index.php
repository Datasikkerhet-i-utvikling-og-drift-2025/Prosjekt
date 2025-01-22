<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/controllers/UserController.php';

use api\controllers\UserController;

$method = $_SERVER['REQUEST_METHOD'];
$route = $_GET['route'] ?? null; // Get the 'route' query parameter

$controller = new UserController();

switch ($route) {
    case 'users':
        if ($method === 'POST') {
            $controller->saveUser();
        } elseif ($method === 'GET') {
            $controller->getAllUsers();
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;

    case 'students':
        if ($method === 'GET') {
            $controller->getAllStudents();
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;

    case 'login':
        if ($method === 'POST') {
            $controller->loginUser();
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
        break;
}
?>