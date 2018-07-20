<?php

namespace Gamma\ErrorsBundle\Subscriber;

use Gamma\ErrorsBundle\Debug\ErrorHandler;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class ErrorHandlerSubscriber.
 */
class ErrorHandlerSubscriber implements EventSubscriberInterface
{
    /** @var ErrorHandler */
    private $errorHandler;
    /** @var bool */
    private $handlerRegistered = false;

    /**
     * ErrorHandlerSubscriber constructor.
     *
     * @param ErrorHandler $errorHandler
     */
    public function __construct(ErrorHandler $errorHandler)
    {
        $this->errorHandler = $errorHandler;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onRequest'],
            KernelEvents::EXCEPTION => ['onKernelException'],
            ConsoleEvents::EXCEPTION => ['onConsoleException'],
        ];
    }

    /**
     * Registers PHP errors handlers.
     *
     * @param GetResponseEvent $event
     */
    public function onRequest(GetResponseEvent $event)
    {
        if (true === $this->handlerRegistered) {
            return;
        }

        $this->handlerRegistered = true;

        $this->errorHandler->register();
    }

    /**
     * Processes uncatched exceptions.
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $this->errorHandler->onException($event->getException());
    }

    /**
     * @param ConsoleExceptionEvent $event
     */
    public function onConsoleException(ConsoleExceptionEvent $event)
    {
        $this->errorHandler->onException($event->getException());
    }
}
