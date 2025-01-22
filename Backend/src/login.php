<?php

use api\controllers\LoginUser;

header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/controllers/LoginUser.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new LoginUser();
    $controller->loginUser();
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}