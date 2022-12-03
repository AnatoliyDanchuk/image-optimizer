<?php

namespace Framework\ExceptionHandler;

use Framework\Endpoint\BundleEndpoint\CheckHealthEndpoint;
use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecificationTemplate;
use Framework\Endpoint\EndpointTemplate\ApplicationHttpEndpointTemplate;
use Framework\Endpoint\EndpointInput\EndpointInputInfoBuilder;
use Framework\ResponseBuilder\InvalidHttpRequestResponseBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

final class InvalidHttpPathHandler implements ExceptionHandlerInterface
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly CheckHealthEndpoint $helpEndpoint,
        private readonly InvalidHttpRequestResponseBuilder $responseBuilder,
        private readonly RouterInterface $router,
        private readonly ContainerInterface $serviceProvider,
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if (!($exception instanceof NotFoundHttpException)) {
            return;
        }
        /** @var NotFoundHttpException $exception */

        $matchedRouteCollectionByPath = $this->getMatchedRouteCollectionByPath($event);
        if ($matchedRouteCollectionByPath->count() > 2) {
            $documentation = '';
            $errorDetails = [
                'reason' => 'Request has not enough signature for binding it to one of found routes.',
                'explanation' => 'Found few routes for your request',
                'expectation' => 'Add at least one of unique params to request'
                    . ' for binding request to expected route.',
                'router' => [
                    'foundRelatedRoutes' => $this->buildFoundRelatedRoutes($matchedRouteCollectionByPath),
                    'violation' => $exception->getMessage(),
                ],
            ];
        } else {
            $documentation = $this->urlGenerator->generate($this->helpEndpoint->getRouteName());
            $errorDetails = [
                'invalidHttpPath' => [
                    'violation' => $exception->getMessage(),
                ],
            ];
        }

        $response = $this->responseBuilder->getResponse($errorDetails, $documentation);
        $event->setResponse($response);
    }

    private function getMatchedRouteCollectionByPath(ExceptionEvent $event): RouteCollection
    {
        $matchedRouteCollectionByPath = new RouteCollection();
        $requestPath = $event->getRequest()->getPathInfo();
        foreach ($this->router->getRouteCollection() as $routeName => $route) {
            if ($route->getPath() === $requestPath) {
                $matchedRouteCollectionByPath->add($routeName, $route);
            }
        }
        return $matchedRouteCollectionByPath;
    }

    private function buildFoundRelatedRoutes(RouteCollection $matchedRouteCollectionByPath): array
    {
        $expectedParamsByRouteName = $this->getExpectedParamsByRouteName($matchedRouteCollectionByPath);
        $uniqueParamsByRouteName = $this->getUniqueParamsByRouteName($expectedParamsByRouteName);

        // Simplify of matching expected routes in tests.
        ksort($uniqueParamsByRouteName);

        $foundRelatedRoutes = [];
        foreach ($uniqueParamsByRouteName as $routeName => $uniqueParams) {
            $foundRelatedRoutes[$routeName] = [
                'uniqueParams' => (new EndpointInputInfoBuilder())->buildParamPathsInfo(...$uniqueParams),
            ];
        }
        return $foundRelatedRoutes;
    }

    /** @return EndpointParamSpecificationTemplate[][] */
    private function getExpectedParamsByRouteName(RouteCollection $routeCollection): array
    {
        $namesOfExpectedParamsByRouteName = [];
        foreach ($routeCollection as $routeName => $route) {
            $endpointClass = $route->getDefault('_controller')[0];
            /** @var ApplicationHttpEndpointTemplate $endpoint */
            $endpoint = $this->serviceProvider->get($endpointClass);
            $namesOfExpectedParamsByRouteName[$routeName] = $endpoint->getExpectedInput()->getEndpointParams();
        }
        return $namesOfExpectedParamsByRouteName;
    }

    /** @return EndpointParamSpecificationTemplate[][] */
    private function getUniqueParamsByRouteName(array $expectedParamsByRouteName): array
    {
        $uniqueParamsByRouteName = [];
        foreach ($expectedParamsByRouteName as $routeName => $expectedParams) {
            $others = array_diff_key($expectedParamsByRouteName, [$routeName => null]);
            /**
             * array_diff does not compare objects as "===" or "==",
             * array_diff compare objects like (string)Object1 == (string)Object2
             * @uses \Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecificationTemplate::__toString()
             */
            $uniqueParamsByRouteName[$routeName] = array_diff($expectedParams, ...array_values($others));
        }
        return $uniqueParamsByRouteName;
    }
}