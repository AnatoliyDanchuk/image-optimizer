<?php

namespace Framework\ExceptionHandler;

use Framework\Endpoint\BundleEndpoint\CheckHealthEndpoint;
use Framework\Endpoint\EndpointTemplate\ApplicationHttpEndpointTemplate;
use Framework\ResponseBuilder\InvalidHttpRequestResponseBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

final class InvalidHttpPathHandler implements ExceptionHandlerInterface
{
    private UrlGeneratorInterface $urlGenerator;
    private CheckHealthEndpoint $helpEndpoint;
    private InvalidHttpRequestResponseBuilder $responseBuilder;
    private RouterInterface $router;
    private ContainerInterface $serviceProvider;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        CheckHealthEndpoint $helpEndpoint,
        InvalidHttpRequestResponseBuilder $responseBuilder,
        RouterInterface $router,
        ContainerInterface $serviceProvider
    ) {
        $this->serviceProvider = $serviceProvider;
        $this->router = $router;
        $this->responseBuilder = $responseBuilder;
        $this->helpEndpoint = $helpEndpoint;
        $this->urlGenerator = $urlGenerator;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if (!($exception instanceof NotFoundHttpException)) {
            return;
        }
        /** @var NotFoundHttpException $exception */

        $matchedRoutesByPath = $this->getMatchedRoutesByPath($event);
        if (!empty($matchedRoutesByPath)) {
            $documentation = '';
            $errorDetails = [
                'reason' => 'Request has not enough signature for binding it to one of found routes.',
                'explanation' => 'Found few routes for your request',
                'expectation' => 'Add at least one of unique params to request'
                    . ' for binding request to expected route.',
                'router' => [
                    'foundRelatedRoutes' => $this->buildFoundRelatedRoutes($matchedRoutesByPath),
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

    private function getMatchedRoutesByPath(ExceptionEvent $event): array
    {
        $matchedRoutesByPath = [];
        $requestPath = $event->getRequest()->getPathInfo();
        foreach ($this->router->getRouteCollection() as $routeName => $route) {
            if ($route->getPath() === $requestPath) {
                $matchedRoutesByPath[$routeName] = $route;
            }
        }
        return $matchedRoutesByPath;
    }

    private function buildFoundRelatedRoutes(array $matchedRoutesByPath): array
    {
        $namesOfExpectedParamsByRouteName = $this->getNamesOfExpectedParamsByRouteName($matchedRoutesByPath);
        $namesOfUniqueParamsByRouteName = $this->getNamesOfUniqueParamsByRouteName($namesOfExpectedParamsByRouteName);

        // Simplify of matching expected routes in tests.
        ksort($namesOfUniqueParamsByRouteName);

        $foundRelatedRoutes = [];
        foreach ($namesOfUniqueParamsByRouteName as $routeName => $namesOfUniqueParams) {
            $foundRelatedRoutes[$routeName] = [
                'uniqueParams' => $namesOfUniqueParams,
            ];
        }
        return $foundRelatedRoutes;
    }

    private function getNamesOfExpectedParamsByRouteName(array $matchedRoutesByPath): array
    {
        $namesOfExpectedParamsByRouteName = [];
        foreach ($matchedRoutesByPath as $routeName => $route) {
            $endpointClass = $route->getDefault('_controller')[0];
            /** @var ApplicationHttpEndpointTemplate $endpoint */
            $endpoint = $this->serviceProvider->get($endpointClass);
            $namesOfExpectedParamsByRouteName[$routeName] = $endpoint->getExpectedInput()->getNamesOfAllParams();
        }
        return $namesOfExpectedParamsByRouteName;
    }

    private function getNamesOfUniqueParamsByRouteName(array $namesOfExpectedParamsByRouteName): array
    {
        $namesOfUniqueParamsByRouteName = [];
        foreach ($namesOfExpectedParamsByRouteName as $routeName => $namesOfExpectedParams) {
            $others = array_diff_key($namesOfExpectedParamsByRouteName, [$routeName => null]);
            $namesOfUniqueParamsByRouteName[$routeName] = array_diff($namesOfExpectedParams, ...array_values($others));
        }
        return $namesOfUniqueParamsByRouteName;
    }
}