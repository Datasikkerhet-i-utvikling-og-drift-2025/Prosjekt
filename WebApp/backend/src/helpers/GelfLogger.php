<?php

namespace helpers;

use Gelf\Message;
use Gelf\Publisher;
use Gelf\Transport\UdpTransport;

/**
 * Class GelfLogger
 * Sends structured logs to Graylog using GELF.
 */
class GelfLogger
{
    private Publisher $publisher;

    public function __construct(string $host = 'graylog', int $port = 12201)
    {
        $transport = new UdpTransport($host, $port);
        $this->publisher = new Publisher($transport);
    }

    public function info(string $shortMessage, array $context = []): void
    {
        $this->log('info', $shortMessage, $context);
    }

    public function debug(string $shortMessage, array $context = []): void
    {
        $this->log('debug', $shortMessage, $context);
    }

    public function warning(string $shortMessage, array $context = []): void
    {
        $this->log('warning', $shortMessage, $context);
    }

    public function error(string $shortMessage, array $context = []): void
    {
        $this->log('error', $shortMessage, $context);
    }

    public function success(string $shortMessage, array $context = []): void
    {
        $this->log('notice', $shortMessage, $context);
    }

    private function log(string $level, string $shortMessage, array $context = []): void
    {
        $message = new Message();
        $message
            ->setShortMessage($shortMessage)
            ->setLevel($this->mapLevel($level))
            ->setFacility('SecureFeedbackApp')
            ->setAdditional('context', $context)
            ->setTimestamp(microtime(true));

        $this->publisher->publish($message);
    }

    private function mapLevel(string $level): int
    {
        return match (strtolower($level)) {
            'debug' => 7,
            'info' => 6,
            'notice', 'success' => 5,
            'warning' => 4,
            'error' => 3,
            default => 1
        };
    }
}
