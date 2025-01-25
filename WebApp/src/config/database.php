<?php

#$host = getenv('DB_HOST');
#$dbname = getenv('DB_NAME');
#$user = getenv('DB_USER');
#$pass = getenv('DB_PASS');



$host = 'mysql';
$dbname = 'database';
$user = 'admin';
$pass = 'admin';


try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

#return $pdo;

return [
    'host' => 'mysql',             // Change 'mysql' to your DB host if different
    'dbname' => 'database', // Your database name
    'username' => 'admin',          // Your database username
    'password' => 'admin',      // Your database password
];

