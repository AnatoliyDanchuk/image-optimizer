<?php

namespace Framework\Config\Routes;

use FilesystemIterator;
use Framework\Endpoint\EndpointTemplate\ApplicationHttpEndpointTemplate;
use Framework\Endpoint\EndpointTemplate\HttpEndpointTemplate;
use LogicException;
use ReflectionClass;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Routing\RouteCollection;

final class HttpEndpointLoader extends FileLoader
{
    private ContainerInterface $serviceProvider;

    public function __construct(
        ContainerInterface $serviceProvider,
        FileLocator $locator
    ) {
        $this->serviceProvider = $serviceProvider;
        parent::__construct($locator);
    }

    public function supports($resource, string $type = null): bool
    {
        return $type === HttpEndpointTemplate::class;
    }

    public function load($resource, string $type = null): RouteCollection
    {
        $routes = $this->loadAllRoutes($resource);
        $this->fixRoutesWithSamePath($routes);

        return $routes;
    }

    public function loadAllRoutes($resource): RouteCollection
    {
        $routes = new RouteCollection();

        $path = $this->locator->locate($resource);
        if (is_dir($path)) {
            $endpointDirectory = new FilesystemIterator($path, FilesystemIterator::CURRENT_AS_PATHNAME);
            foreach ($endpointDirectory as $endpointFile) {
                $directoryRoutes = $this->loadAllRoutes($endpointFile);
                $routes->addCollection($directoryRoutes);
            }
        } else {
            $class = $this->getFirstFullClassName($path);

            $classIsNotTemplate = (new ReflectionClass($class))->isFinal();
            $classExtendedEndpointTemplate = is_subclass_of($class, HttpEndpointTemplate::class);
            if ($classExtendedEndpointTemplate && $classIsNotTemplate) {
                /** @var HttpEndpointTemplate $endpoint */
                $endpoint = $this->serviceProvider->get($class);
                $routes->add($endpoint->getRouteName(), $endpoint->getImmediatelyRoute());
                $routes->add('defer_' . $endpoint->getRouteName(), $endpoint->getDeferRoute());
            }
            gc_mem_caches();
        }

        return $routes;
    }

    protected function getFirstFullClassName(string $file): string
    {
        $class = false;
        $namespace = false;
        $tokens = token_get_all(file_get_contents($file));

        $nsTokens = [T_NS_SEPARATOR => true, T_STRING => true];
        if (defined('T_NAME_QUALIFIED')) {
            $nsTokens[T_NAME_QUALIFIED] = true;
        }
        for ($i = 0; isset($tokens[$i]); ++$i) {
            $token = $tokens[$i];
            if (!isset($token[1])) {
                continue;
            }

            if (true === $class && T_STRING === $token[0]) {
                $firstFullClassName = $namespace.'\\'.$token[1];
                break;
            }

            if (true === $namespace && isset($nsTokens[$token[0]])) {
                $namespace = $token[1];
                $token = $tokens[++$i];
            }

            if (T_CLASS === $token[0]) {
                $class = true;
            }

            if (T_NAMESPACE === $token[0]) {
                $namespace = true;
            }
        }

        return $firstFullClassName ?? throw new LogicException("File $file is not correct HttpEndpoint.");
    }

    private function fixRoutesWithSamePath(RouteCollection $routes): void
    {
        foreach ($this->getNamesOfRoutesBySamePath($routes) as $path => $namesOfRoutes) {
            $namesOfExpectedParamsByRouteName = $this->indexNamesOfExpectedParamsByRouteName($namesOfRoutes, $routes);

            $routeNamesWithoutUniqueParams = [];
            foreach ($namesOfExpectedParamsByRouteName as $currentRouteName => $currentRouteParams) {
                $paramsOfOtherRoutes = array_diff_key($namesOfExpectedParamsByRouteName, [$currentRouteName => null]);
                $namesOfUniqueParams = array_diff($currentRouteParams, ...array_values($paramsOfOtherRoutes));

                if (!empty($namesOfUniqueParams)) {
                    $condition = $this->buildRouteCondition($namesOfUniqueParams);
                    $route = $routes->get($currentRouteName);
                    $route->setCondition($condition);
                } else {
                    $routeNamesWithoutUniqueParams[] = $currentRouteName;
                }
            }

            if (!empty($routeNamesWithoutUniqueParams)) {
                // Simplify matching routes in tests.
                sort($routeNamesWithoutUniqueParams);

                switch (count($routeNamesWithoutUniqueParams)) {
                    case 1:
                        throw new LogicException("Route $routeNamesWithoutUniqueParams[0] has not unique signature");
                    default:
                        throw new LogicException("Path $path has routes without unique signature."
                        . ' Related routes: ' . implode(', ', $routeNamesWithoutUniqueParams) . '.'
                    );
                }
            }
        }
    }

    private function getNamesOfRoutesBySamePath(RouteCollection $routes): array
    {
        $routesByPath = [];
        foreach ($routes->all() as $routeName => $route) {
            $routesByPath = array_merge_recursive($routesByPath, [
                $route->getPath() => $routeName,
            ]);
        }
        return array_filter($routesByPath, 'is_array');
    }

    private function indexNamesOfExpectedParamsByRouteName(array $namesOfRoutes, RouteCollection $routes): array
    {
        $namesOfExpectedParamsByRouteName = [];
        foreach ($namesOfRoutes as $routeName) {
            $endpointClass = $routes->get($routeName)->getDefault('_controller')[0];
            /** @var ApplicationHttpEndpointTemplate $endpoint */
            $endpoint = $this->serviceProvider->get($endpointClass);
            $namesOfExpectedParamsByRouteName[$routeName] = $endpoint->getExpectedInput()->getNamesOfAllParams();
        }
        return $namesOfExpectedParamsByRouteName;
    }

    private function buildRouteCondition(array $namesOfParams): string
    {
        $conditionChecks = [];
        foreach ($namesOfParams as $paramName) {
            $conditionChecks[] = "request.query.has('$paramName')";
        }

        return implode(" || ", $conditionChecks);
    }
}
