<?php

class Logger
{
    private static $logFile = __DIR__ . '/../../logs/app.log'; // Default log file path

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
        self::rotateLogFile();

        $date = date('Y-m-d H:i:s'); // Current timestamp
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $file = $backtrace[0]['file'] ?? 'unknown';
        $line = $backtrace[0]['line'] ?? 'unknown';

        $logEntry = "[$date] [$level] $message [in $file:$line]" . PHP_EOL;

        try {
            file_put_contents(self::$logFile, $logEntry, FILE_APPEND | LOCK_EX);
        } catch (Exception $e) {
            error_log("Logger error: Unable to write to log file. " . $e->getMessage());
        }
    }

    // Rotate the log file if it exceeds a certain size
    private static function rotateLogFile()
    {
        if (file_exists(self::$logFile) && filesize(self::$logFile) > 5 * 1024 * 1024) { // 5 MB
            $newFileName = self::$logFile . '.' . date('Y-m-d_H-i-s');
            rename(self::$logFile, $newFileName);
        }
    }
}
