<?php

namespace config;

return [
    // Application Settings
    'app_name' => 'Feedback System',
    'base_url' => 'http://localhost:8080', // Change to your domain in production
    //'base_url' => 'http://http://158.39.188.204/steg1/',
    'environment' => 'development', // Options: 'development', 'production'
    //'environment' => 'production', // Options: 'development', 'production'
    'debug' => true, // Enable or disable debug mode

    // DatabaseManager Settings
    'database' => [
        'host' => getenv('DB_HOST'),
        'port' => getenv('DB_PORT'),
        'name' => getenv('DB_NAME'),
        'username' => getenv('DB_USERNAME'),
        'password' => getenv('DB_PASSWORD'),
    ],

    // Email Settings
    'mail' => [
        'host' => getenv('MAIL_HOST'),
        'port' => getenv('MAIL_PORT'),
        'username' => getenv('MAIL_USERNAME'),
        'password' => getenv('MAIL_PASSWORD'),
        'from_address' => getenv('MAIL_FROM_ADDRESS'),
        'from_name' => getenv('MAIL_FROM_NAME'),
    ],

    // Logging Settings
    'log_file' => '../logs/app.log',

    // Security Settings
    'jwt_secret' => getenv('JWT_SECRET') ?: 'your_jwt_secret', // If you're using JWT for authentication
    'csrf_token_name' => 'csrf_token', // CSRF token name for forms

    // Other App Configurations
    'pagination' => [
        'items_per_page' => 10,
    ],
];
