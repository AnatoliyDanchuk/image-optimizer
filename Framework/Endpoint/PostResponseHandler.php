<?php

namespace Framework\Endpoint;

use Framework\Endpoint\EndpointTemplate\HttpEndpointTemplate;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class PostResponseHandler
{
    public function onKernelTerminate(TerminateEvent $event): void
    {
        $request = $event->getRequest();
        $uri = $request->server->get('REQUEST_URI');
        if (str_starts_with($uri, '/' . HttpEndpointTemplate::DEFER_RUN . '/')) {
            $server = array_merge($request->server->all(), [
                'REQUEST_URI' => str_replace(
                    '/' . HttpEndpointTemplate::DEFER_RUN . '/',
                    '/',
                    $uri
                ),
            ]);

            $subRequest = $request->duplicate(null, null, [], null, null, $server);

            $event->getKernel()->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }
    }
}