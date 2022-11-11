<?php

namespace Framework\Endpoint\EndpointTemplate;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;

abstract class HttpEndpointTemplate
{
    public const DEFER_RUN = 'defer';

    private bool $runInDeferMode;

    final public function getImmediatelyRoute(): Route
    {
        return new Route(
            $this->getHttpPath(),
            ['_controller' => [static::class, 'immediatelyHandleRequest']],
            [],
            [],
            '',
            [],
            [$this->getHttpMethod()],
        );
    }

    final public function getDeferRoute(): Route
    {
        return new Route(
            '/' . self::DEFER_RUN . $this->getHttpPath(),
            ['_controller' => [static::class, 'deferHandleRequest']],
            [],
            [],
            '',
            [],
            [$this->getHttpMethod()],
        );
    }

    abstract protected function getHttpMethod(): string;

    abstract protected function getHttpPath(): string;

    public function immediatelyHandleRequest(Request $request): Response
    {
        $this->runInDeferMode = false;
        return $this->handleRequest($request);
    }

    public function deferHandleRequest(Request $request): Response
    {
        $this->runInDeferMode = true;
        return $this->handleRequest($request);
    }

    abstract protected function handleRequest(Request $request): Response;

    final public function getRouteName(): string
    {
        return static::class;
    }

    public function isRunInDeferMode(): bool
    {
        return $this->runInDeferMode;
    }
}