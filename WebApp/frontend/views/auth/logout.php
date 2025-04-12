<?php

//require_once __DIR__ . '/../../vendor/autoload.php';

use managers\SessionManager;

// Start session (in case it's not started)
$sessionManager = new SessionManager();
$sessionManager->destroy();

// Optional: legg inn en suksessmelding hvis du bruker $_SESSION['success']
session_start(); // Trengs på nytt etter destroy for å sette ny melding
$_SESSION['success'] = 'You have been logged out successfully.';

header('Location: /'); // Send bruker til forsiden eller login
exit;
