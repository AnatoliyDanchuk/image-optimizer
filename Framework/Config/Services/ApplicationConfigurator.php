<?php

namespace Framework\Config\Services;

use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

final class ApplicationConfigurator
{
    public function configure(ServicesConfigurator $servicesConfigurator): void
    {
        $servicesConfigurator->load('Domain\\', __DIR__ . '/../../../Domain/');
        $servicesConfigurator->load('DomainAdapter\\', __DIR__ . '/../../../DomainAdapter/');
        $servicesConfigurator->load('Api\\', __DIR__ . '/../../../Api/')
            ->public();

        $this->enableApplicationFactories($servicesConfigurator);
    }

    private function enableApplicationFactories(ServicesConfigurator $servicesConfigurator): void
    {
        $directory = __DIR__ . '/../../../Api/EndpointServiceFactory';
        $factoryFiles = new \RecursiveDirectoryIterator($directory,
            \FilesystemIterator::CURRENT_AS_PATHNAME
            | \FilesystemIterator::SKIP_DOTS);

        $replacement = [
            __DIR__ . '/../../../' => '',
            '.php' => '',
            '/' => '\\',
        ];
        foreach ($factoryFiles as $factoryFile) {
            $factoryClass = str_replace(array_keys($replacement), array_values($replacement), $factoryFile);

            $reflectionClass = new \ReflectionClass($factoryClass);
            /** @noinspection NullPointerExceptionInspection */
            $serviceClass = $reflectionClass->getMethod('__invoke')->getReturnType()->getName();

            $servicesConfigurator->set($serviceClass)
                ->factory(service($factoryClass))
                ->lazy();
        }
    }
}