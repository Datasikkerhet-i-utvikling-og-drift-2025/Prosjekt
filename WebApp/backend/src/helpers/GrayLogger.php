<?php
namespace helpers;

require_once __DIR__ . '/../../vendor/autoload.php';

use Gelf\Message;
use Gelf\Publisher;
use Gelf\Transport\UdpTransport;
use RuntimeException;
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
     * @var Publisher|null
     */
    private ?Publisher $publisher = null;

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
            $this->fallbackLog("[GrayLogger] Transport initialization failed: " . $e->getMessage());
            $this->publisher = null;
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
     * Sends a log message to Graylog or fallback to file.
     *
     * @param string $shortMessage Short summary of the log event.
     * @param array $context Additional key-value metadata.
     * @param string $level Log severity level (e.g. info, error, debug).
     * @return void
     */
    public function log(string $shortMessage, array $context = [], string $level = 'info'): void
    {
        try {
            if ($this->publisher === null) {
                throw new RuntimeException("Graylog publisher is not initialized.");
            }

            $message = new Message();
            $message->setShortMessage($shortMessage);
            $message->setLevel($this->mapLogLevel($level));
            $message->setTimestamp(microtime(true));
            $message->setHost('backend');

            foreach ($context as $key => $value) {
                $message->setAdditional($key, $value);
            }

            $caller = $this->getCallerInfo();
            $message->setAdditional('file', $caller['file']);
            $message->setAdditional('line', $caller['line']);

            $this->publisher->publish($message);
        } catch (Throwable $e) {
            $this->fallbackLog("[GrayLogger] Logging failed: " . $e->getMessage(), [
                'shortMessage' => $shortMessage,
                'context' => $context,
                'level' => $level
            ]);
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
     * @return array{file: string, line: int}
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
     * Writes log message to fallback file.
     *
     * @param string $error Error message
     * @param array|null $data Optional log data
     * @return void
     */
    private function fallbackLog(string $error, ?array $data = null): void
    {
        $log = [
            'timestamp' => date('c'),
            'error' => $error,
            'data' => $data
        ];
        file_put_contents('/var/www/html/logs/log-fallback.log', json_encode($log, JSON_PRETTY_PRINT) . PHP_EOL, FILE_APPEND);
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

    /**
     * Logs a notice-level message.
     *
     * @param string $message Log message.
     * @param array $context Additional metadata.
     * @return void
     */
    public function notice(string $message, array $context = []): void
    {
        $this->log($message, $context, 'notice');
    }

    /**
     * Logs a critical-level message.
     *
     * @param string $message Log message.
     * @param array $context Additional metadata.
     * @return void
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log($message, $context, 'critical');
    }

    /**
     * Logs an alert-level message.
     *
     * @param string $message Log message.
     * @param array $context Additional metadata.
     * @return void
     */
    public function alert(string $message, array $context = []): void
    {
        $this->log($message, $context, 'alert');
    }

    /**
     * Logs an emergency-level message.
     *
     * @param string $message Log message.
     * @param array $context Additional metadata.
     * @return void
     */
    public function emergency(string $message, array $context = []): void
    {
        $this->log($message, $context, 'emergency');
    }

}