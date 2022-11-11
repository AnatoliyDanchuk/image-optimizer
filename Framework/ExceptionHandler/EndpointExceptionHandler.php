<?php

namespace Framework\ExceptionHandler;

use Framework\Exception\EndpointException;
use Framework\ResponseBuilder\ExceptionResponseBuilder;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

final class EndpointExceptionHandler implements ExceptionHandlerInterface
{
    private ExceptionResponseBuilder $responseBuilder;

    public function __construct(
        ExceptionResponseBuilder $responseBuilder
    ) {
        $this->responseBuilder = $responseBuilder;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if (!($exception instanceof EndpointException)) {
            return;
        }

        $response = $this->responseBuilder->getResponse($exception);

        $event->setResponse($response);
    }
}