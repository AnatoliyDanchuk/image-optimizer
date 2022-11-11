<?php

namespace Framework\Endpoint\BundleEndpoint;

use Framework\Endpoint\EndpointTemplate\HttpEndpointTemplate;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Required supporting HTTP GET / with empty body
 * for checking health of webservice by current tool.
 */
final class CheckHealthEndpoint extends HttpEndpointTemplate
{
    protected function getHttpMethod(): string
    {
        return Request::METHOD_GET;
    }

    protected function getHttpPath(): string
    {
        return '/';
    }

    protected function handleRequest(Request $request): Response
    {
        // todo: api auto doc and ui. Maybe use API Platform for Symfony.
        return new Response();
    }
}