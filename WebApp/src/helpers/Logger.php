<?php

class Logger
{
    private static $logFile = '../logs/app.log'; // Default log file path

    // Set a custom log file (optional)
    public static function setLogFile($filePath)
    {
        self::$logFile = $filePath;
    }

    // Log an informational message
    public static function info($message)
    {
        self::writeLog('INFO', $message);
    }

    // Log a warning message
    public static function warning($message)
    {
        self::writeLog('WARNING', $message);
    }

    // Log an error message
    public static function error($message)
    {
        self::writeLog('ERROR', $message);
    }

    // Log a critical error message
    public static function critical($message)
    {
        self::writeLog('CRITICAL', $message);
    }

    // Generic method to write to the log file
    private static function writeLog($level, $message)
    {
        $date = date('Y-m-d H:i:s'); // Current timestamp
        $logEntry = "[$date] [$level] $message" . PHP_EOL;

        // Write to the log file
        try {
            file_put_contents(self::$logFile, $logEntry, FILE_APPEND | LOCK_EX);
        } catch (Exception $e) {
            // If writing to the log fails, output a fallback error (optional)
            error_log("Logger error: Unable to write to log file. " . $e->getMessage());
        }
    }
}
