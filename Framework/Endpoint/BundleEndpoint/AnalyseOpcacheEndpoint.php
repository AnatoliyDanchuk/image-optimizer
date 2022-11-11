<?php

namespace Framework\Endpoint\BundleEndpoint;

use Framework\Endpoint\EndpointTemplate\HttpEndpointTemplate;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AnalyseOpcacheEndpoint extends HttpEndpointTemplate
{
    protected function getHttpMethod(): string
    {
        return Request::METHOD_GET;
    }

    protected function getHttpPath(): string
    {
        return '/analyse_opcache';
    }

    protected function handleRequest(Request $request): Response
    {
        /** @noinspection SpellCheckingInspection */
        $htmlReport = include __DIR__ . "/../../../var/vendor/amnuts/opcache-gui/index.php";
        return new Response($htmlReport);
    }
}