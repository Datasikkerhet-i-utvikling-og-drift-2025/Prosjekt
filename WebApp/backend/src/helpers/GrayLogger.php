<?php

namespace helpers;

use Gelf\Message;
use Gelf\Publisher;
use Gelf\Transport\UdpTransport;
use Throwable;

/**
 * Class GrayLogger
 *
 * Handles structured logging to Graylog using UDP GELF transport.
 * Provides a singleton logger instance with severity-level methods.
 *
 * @package helpers
 */
class GrayLogger
{
    /**
     * Singleton instance of the logger.
     *
     * @var GrayLogger|null
     */
    private static ?self $instance = null;

    /**
     * GELF message publisher.
     *
     * @var Publisher
     */
    private Publisher $publisher;

    /**
     * GrayLogger constructor.
     * Initializes the GELF transport and publisher.
     *
     * @param string $host Graylog host (default: graylog)
     * @param int $port GELF UDP port (default: 12201)
     */
    private function __construct(string $host = 'graylog', int $port = 12201)
    {
        try {
            $transport = new UdpTransport($host, $port);
            $this->publisher = new Publisher($transport);
        } catch (Throwable $e) {
            error_log("[GrayLogger] Transport initialization failed: " . $e->getMessage());
        }
    }

    /**
     * Returns the singleton instance of the logger.
     *
     * @return GrayLogger
     */
    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    /**
     * Sends a log message to Graylog.
     *
     * @param string $shortMessage Short summary of the log event.
     * @param array $context Additional key-value metadata.
     * @param string $level Log severity level (e.g. info, error, debug).
     * @return void
     */
    public function log(string $shortMessage, array $context = [], string $level = 'info'): void
    {
        try {
            $message = new Message();
            $message->setShortMessage($shortMessage);
            $message->setLevel($this->mapLogLevel($level));
            $message->setTimestamp(microtime(true));
            $message->setFacility('SecureFeedbackApp');
            $message->setHost('backend');

            foreach ($context as $key => $value) {
                $message->setAdditional($key, $value);
            }

            $caller = $this->getCallerInfo();
            $message->setAdditional('file', $caller['file']);
            $message->setAdditional('line', $caller['line']);

            $this->publisher->publish($message);
        } catch (Throwable $e) {
            error_log("[GrayLogger] Logging failed: " . $e->getMessage());
        }
    }

    /**
     * Maps a textual log level to its syslog integer equivalent.
     *
     * @param string $level Log level name.
     * @return int Syslog level constant.
     */
    private function mapLogLevel(string $level): int
    {
        return match (strtolower($level)) {
            'debug'     => LOG_DEBUG,
            'info'      => LOG_INFO,
            'notice'    => LOG_NOTICE,
            'warning'   => LOG_WARNING,
            'error'     => LOG_ERR,
            'critical'  => LOG_CRIT,
            'alert'     => LOG_ALERT,
            'emergency' => LOG_EMERG,
            default     => LOG_INFO,
        };
    }

    /**
     * Retrieves the file and line where the log call originated.
     *
     * @return array{file: string, line: int} Associative array with file and line number.
     */
    private function getCallerInfo(): array
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        foreach ($trace as $frame) {
            if (isset($frame['file']) && !str_contains($frame['file'], 'GrayLogger.php')) {
                return [
                    'file' => basename($frame['file']),
                    'line' => $frame['line'] ?? 0,
                ];
            }
        }

        return ['file' => 'unknown', 'line' => 0];
    }

    /**
     * Logs an informational message.
     *
     * @param string $message Log message.
     * @param array $context Additional metadata.
     * @return void
     */
    public function info(string $message, array $context = []): void
    {
        $this->log($message, $context, 'info');
    }

    /**
     * Logs a debug-level message.
     *
     * @param string $message Log message.
     * @param array $context Additional metadata.
     * @return void
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log($message, $context, 'debug');
    }

    /**
     * Logs a warning-level message.
     *
     * @param string $message Log message.
     * @param array $context Additional metadata.
     * @return void
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log($message, $context, 'warning');
    }

    /**
     * Logs an error-level message.
     *
     * @param string $message Log message.
     * @param array $context Additional metadata.
     * @return void
     */
    public function error(string $message, array $context = []): void
    {
        $this->log($message, $context, 'error');
    }
}
