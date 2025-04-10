<?php

namespace helpers;

use Gelf\Message;
use Gelf\Publisher;
use Gelf\Transport\UdpTransport;
use Gelf\Transport\IgnoreErrorTransportWrapper;
use Throwable;

/**
 * Class GraylogLogger
 *
 * Håndterer logging til Graylog via UDP GELF.
 * Trygg og enkel måte å sende structured logs fra PHP til Graylog-tjener.
 *
 * @package App\Helpers
 */
class GrayLogger
{
    /**
     * Singleton-instans
     *
     * @var GrayLogger|null
     */
    private static ?GrayLogger $instance = null;

    /**
     * Graylog publisher
     *
     * @var Publisher
     */
    private Publisher $publisher;

    /**
     * GraylogLogger constructor.
     * Oppretter transport og publisher for GELF.
     *
     * @param string $host Graylog host (default: graylog)
     * @param int $port GELF UDP port (default: 12201)
     */
    private function __construct(string $host = 'graylog', int $port = 12201)
    {
        try {
            $transport = new IgnoreErrorTransportWrapper(new UdpTransport($host, $port));
            $this->publisher = new Publisher($transport);
        } catch (Throwable $e) {
            error_log("Graylog transport init feilet: " . $e->getMessage());
        }
    }

    /**
     * Returnerer singleton-instansen av loggeren.
     *
     * @return GrayLogger
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Logger en melding til Graylog.
     *
     * @param string $shortMessage En kort beskrivelse av hendelsen.
     * @param array $context Ekstra metadata (key => value).
     * @param string $level Loggnivå (info, error, debug, warning, etc).
     * @return void
     */
    public function log(string $shortMessage, array $context = [], string $level = 'info'): void
    {
        try {
            $message = new Message();
            $message->setShortMessage($shortMessage);
            $message->setLevel($this->mapLogLevel($level));
            $message->setTimestamp(time());
            $message->setHost(gethostname());

            // Legg til metadata
            foreach ($context as $key => $value) {
                $message->setAdditional($key, $value);
            }

            // Legg til info om hvor i koden loggen kom fra
            $caller = $this->getCallerInfo();
            $message->setAdditional('file', $caller['file']);
            $message->setAdditional('line', $caller['line']);

            $this->publisher->publish($message);
        } catch (Throwable $e) {
            error_log("Graylog-logging feilet: " . $e->getMessage());
        }
    }

    /**
     * Mapper tekstnivå til syslog-nivå.
     *
     * @param string $level
     * @return int
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
     * Returnerer filnavn og linje fra der loggen ble kalt.
     *
     * @return array{file: string, line: int}
     */
    private function getCallerInfo(): array
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        if (isset($backtrace[2])) {
            return [
                'file' => $backtrace[2]['file'] ?? 'unknown',
                'line' => $backtrace[2]['line'] ?? 0,
            ];
        }
        return ['file' => 'unknown', 'line' => 0];
    }


    public function info(string $message, array $context = []): void
    {
        $this->log($message, $context, 'info');
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log($message, $context, 'debug');
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log($message, $context, 'warning');
    }

    public function error(string $message, array $context = []): void
    {
        $this->log($message, $context, 'error');
    }

}
