<?php

namespace Framework\Config\Services;

use Framework\Config\Routes\HttpEndpointLoader;
use Framework\Endpoint\JsonRequestTransformer;
use Framework\Endpoint\PostResponseHandler;
use Framework\ExceptionHandler\ExceptionHandlerInterface;
use Framework\ExceptionHandler\InvalidHttpPathHandler;
use Framework\ExceptionHandler\UnexpectedEndpointErrorHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Symfony\Component\HttpKernel\KernelEvents;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

final class FrameworkConfigurator
{
    public function configure(ServicesConfigurator $servicesConfigurator): void
    {
        // "instanceof" should be before "load"
        $servicesConfigurator->instanceof(ExceptionHandlerInterface::class)
            ->tag('kernel.event_listener', ['event' => KernelEvents::EXCEPTION]);

        $servicesConfigurator->load('Framework\\', __DIR__ . '/../../../Framework/')
            ->exclude(__DIR__ . '/../../../Framework/'.'{entrypoint}/*');

        $servicesConfigurator->load('Framework\\Endpoint\\', __DIR__ . '/../../../Framework/Endpoint/*')
            ->exclude(__DIR__ . '/../../../Framework/Endpoint/EndpointInput/*')
            ->public();

        $servicesConfigurator->set(JsonRequestTransformer::class)
            // use 257 because request transformer should be run before RouterListener
            // Symfony\Component\HttpKernel\EventListener\ValidateRequestListener::onKernelRequest() has priority 256
            ->tag('kernel.event_listener', ['event' => KernelEvents::REQUEST, 'priority' => 257]);

        $servicesConfigurator->set(PostResponseHandler::class)
            ->tag('kernel.event_listener', ['event' => KernelEvents::TERMINATE]);

        # The priorities of the internal Symfony listeners usually range from -256 to 256
        # but your own listeners can use any positive or negative integer.
        # Thus, priority "257" should without fail override default symfony handler for 500 Internal Error.
        $servicesConfigurator->set(UnexpectedEndpointErrorHandler::class)
            ->tag('kernel.event_listener', ['event' => KernelEvents::EXCEPTION, 'priority' => 257]);

        $servicesConfigurator->set(InvalidHttpPathHandler::class)
            ->arg(ContainerInterface::class, service('service_container'));

        $servicesConfigurator->set(HttpEndpointLoader::class)
            ->arg(ContainerInterface::class, service('service_container'))
            ->tag('routing.loader');
    }
}