<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class TraceIdMiddleware implements EventSubscriberInterface
{
    public function __construct(private LoggerInterface $logger)
    {
        $this->logger->error('TRACE_ID_MIDDLEWARE: Constructed');
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 200],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $this->logger->error('TRACE_ID_MIDDLEWARE: onKernelRequest called!');

        $request = $event->getRequest();

        $traceId = $request->headers->get('X-Trace-ID') ?: uniqid('trace-', true);

        $this->logger->error('TRACE_ID_MIDDLEWARE: Generated trace ID: ' . $traceId);

        $request->headers->set('X-Trace-ID', $traceId);
        $request->attributes->set('trace_id', $traceId);
    }
}
