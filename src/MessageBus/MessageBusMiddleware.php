<?php

namespace Jcid\SimpleBusLogBridge\MessageBus;

use Psr\Log\LoggerInterface;
use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware as BaseMessageBusMiddleware;
use SimpleBus\Message\Message;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

class MessageBusMiddleware implements BaseMessageBusMiddleware
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @var Stopwatch
     */
    private $stopwatch;

    /**
     * @param LoggerInterface $logger
     * @param bool            $debug
     * @param bool            $profile
     */
    public function __construct(LoggerInterface $logger, $debug = false, $profile = false)
    {
        $this->logger = $logger;
        $this->debug = $debug;

        if ($profile) {
            $this->stopwatch = new Stopwatch();
        }
    }

    /**
     * @param Message  $message
     * @param callable $next
     */
    public function handle(Message $message, callable $next)
    {
        if ($this->debug) {
            $this->logger->info("Start handeling message", $this->getLogContext($message));

            if ($this->stopwatch) {
                $this->stopwatch->start("command");
            }
        }

        $next($message);

        if ($this->debug) {
            $event = $this->stopwatch ? $this->stopwatch->stop("command") : null;
            $this->logger->info("Finished handeling message", $this->getLogContext($message, $event));
        }
    }

    /**
     * @param  Message        $message
     * @param  StopwatchEvent $event
     * @return array
     */
    private function getLogContext(Message $message, StopwatchEvent $event = null)
    {
        return [
            "message"   => $message,
            "time"      => $event ? $event->getDuration() : null,
            "memory"    => $event ? $event->getMemory() : null,
            "channel"   => "simplebus",
        ];
    }
}
