<?php

namespace helpers;

use Exception;

class Logger
{
    private static string $logFile = __DIR__ . '/../../logs/app.log'; // Default log file path
    private static int $maxLogSize = 5 * 1024 * 1024; // 5 MB log rotation limit
    private static int $maxLogFiles = 5; // Keep only the last 5 logs

    /**
     * Set a custom log file (optional).
     *
     * @param string $filePath The custom log file path.
     */
    public static function setLogFile(string $filePath): void
    {
        self::$logFile = $filePath;
    }

    /**
     * Log an informational message.
     *
     * @param string $message The log message.
     */
    public static function info(string $message): void
    {
        self::writeLog('INFO', $message);
    }

    /**
     * Log a warning message.
     *
     * @param string $message The log message.
     */
    public static function warning(string $message): void
    {
        self::writeLog('WARNING', $message);
    }

    /**
     * Log an error message.
     *
     * @param string $message The log message.
     */
    public static function error(string $message): void
    {
        self::writeLog('ERROR', $message);
    }

    /**
     * Log a critical error message (requires immediate attention).
     *
     * @param string $message The log message.
     */
    public static function critical(string $message): void
    {
        self::writeLog('CRITICAL', $message);
    }

    /**
     * Log a successful operation message.
     *
     * @param string $message The log message.
     */
    public static function success(string $message): void
    {
        self::writeLog('SUCCESS', $message);
    }

    /**
     * Log a debug message (low-level logs for debugging).
     *
     * @param string $message The log message.
     */
    public static function debug(string $message): void
    {
        self::writeLog('DEBUG', $message);
    }

    /**
     * Log an emergency message (system is unusable).
     *
     * @param string $message The log message.
     */
    public static function emergency(string $message): void
    {
        self::writeLog('EMERGENCY', $message);
    }

    /**
     * Write a log message to the log file.
     *
     * @param string $level The log level (INFO, ERROR, etc.).
     * @param string $message The log message.
     */
    private static function writeLog(string $level, string $message): void
    {
        self::rotateLogFile();

        $date = date('Y-m-d H:i:s'); // Current timestamp
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $file = $backtrace[0]['file'] ?? 'unknown';
        $line = $backtrace[0]['line'] ?? 'unknown';

        $logEntry = "[$date] [$level] $message [in $file:$line]" . PHP_EOL;

        try {
            file_put_contents(self::$logFile, $logEntry, FILE_APPEND | LOCK_EX);
        } catch (Exception $e) {
            error_log("Logger error: Unable to write to log file. " . $e->getMessage());
        }
    }

    /**
     * Rotate the log file if it exceeds the maximum size.
     */
    private static function rotateLogFile(): void
    {
        if (file_exists(self::$logFile) && filesize(self::$logFile) > self::$maxLogSize) {
            $logDir = dirname(self::$logFile);
            $timestamp = date('Y-m-d_H-i-s');
            $newFileName = $logDir . '/app_' . $timestamp . '.log';

            rename(self::$logFile, $newFileName);

            self::cleanupOldLogs($logDir);
        }
    }

    /**
     * Cleanup old logs and keep only the last few log files.
     *
     * @param string $logDir The log directory.
     */
    private static function cleanupOldLogs(string $logDir): void
    {
        $logFiles = glob($logDir . '/app_*.log');
        if (count($logFiles) > self::$maxLogFiles) {
            usort($logFiles, static fn($a, $b) => filemtime($a) - filemtime($b));

            while (count($logFiles) > self::$maxLogFiles) {
                unlink(array_shift($logFiles));
            }
        }
    }

    /**
     * Log message to browser console (useful for debugging in frontend applications).
     *
     * @param string $message The log message.
     */
    public static function logToConsole(string $message): void
    {
        echo "<script>console.log(" . json_encode($message) . ");</script>";
    }

    /**
     * Retrieve the last N log entries.
     *
     * @param int $lines The number of log lines to retrieve.
     * @return array The last N log lines.
     */
    public static function getLogs(int $lines = 50): array
    {
        if (!file_exists(self::$logFile)) {
            return [];
        }

        $file = file(self::$logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return array_slice($file, -$lines);
    }
}
