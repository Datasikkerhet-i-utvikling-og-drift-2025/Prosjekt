<?php
require_once __DIR__ . '/vendor/autoload.php';

use Gelf\Message;
use Gelf\Publisher;
use Gelf\Transport\UdpTransport;

// Endre til riktig IP eller host hvis nødvendig
$graylogHost = 'graylog';
$graylogPort = 12201;

try {
    $transport = new UdpTransport($graylogHost, $graylogPort);
    $publisher = new Publisher($transport);

    $message = new Message();
    $message
        ->setShortMessage('yoyo pang pang')
        ->setLevel(6)
        ->setFacility('SecureFeedbackApp-Test')
        ->setAdditional('debug', 'this is just a test message')
        ->setTimestamp(microtime(true));

    $publisher->publish($message);

    echo "Message sent successfully to Graylog!\n";
} catch (Exception $e) {
    echo "Failed to send log: " . $e->getMessage() . "\n";
}
