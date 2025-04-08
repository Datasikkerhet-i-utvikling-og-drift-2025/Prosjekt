<?php

require_once 'vendor/autoload.php';


use Monolog\Logger;
use Monolog\Handler\GelfHandler;
use Gelf\Publisher;
use Gelf\Transport\UdpTransport;

/**
 * Oppretter en logginstans som sender til Graylog
 * 
 * @param string $name Navnet på loggeren, typisk applikasjonsnavn
 * @param string $host Graylog server host (standard: localhost)
 * @param int $port Graylog GELF UDP port (standard: 12201)
 * @return \Monolog\Logger
 */
function createGraylogLogger(
    string $name = 'feedbacksystem',
    string $host = 'localhost',
    int $port = 12201
): Logger {
    // Oppsett av GELF transport for UDP
    $transport = new UdpTransport($host, $port);
    $publisher = new Publisher($transport);
    $handler = new GelfHandler($publisher);
    
    // Oppretter Logger med GELF handler
    $logger = new Logger($name);
    $logger->pushHandler($handler);
    
    return $logger;
}

// Eksempel på bruk:
$logger = createGraylogLogger('min-php-app');

// Legg til ekstra informasjon som din applikasjon kan trenge
$logger->pushProcessor(function ($record) {
    // Legg til ekstra metadata her
    $record['extra']['php_version'] = PHP_VERSION;
    $record['extra']['server'] = gethostname();
    
    // Du kan legge til databaseinfo eller annen kontekst her
    // $record['extra']['database'] = 'mysql_main';
    
    return $record;
});

// Eksporterer loggeren så den kan brukes i andre filer
return $logger;