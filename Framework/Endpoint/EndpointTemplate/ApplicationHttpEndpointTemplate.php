<?php

namespace Framework\Endpoint\EndpointTemplate;

use Domain\Exception\DomainException;
use Framework\Endpoint\EndpointInput\AppliedInput;
use Framework\Endpoint\EndpointInput\AppliedParam;
use Framework\Endpoint\EndpointInput\ExpectedInput;
use Framework\Endpoint\EndpointInput\FilledExpectedInput;
use Framework\Endpoint\EndpointInput\FoundParam;
use Framework\Endpoint\EndpointInput\IgnoredInput;
use Framework\Endpoint\EndpointInput\IgnoredParam;
use Framework\Endpoint\EndpointInput\ParamPlace;
use Framework\Endpoint\EndpointParamSpecification\InHttpUrlQueryAllowed;
use Framework\Endpoint\EndpointParamSpecification\InJsonHttpBodyAllowed;
use Framework\Exception\EndpointException;
use Framework\Exception\ExceptionWithContext;
use Framework\Exception\FailedEndpointParamError;
use Framework\Exception\InvalidEndpointInputException;
use Framework\Exception\InvalidEndpointParamException;
use Framework\Exception\ParamIsNotAllowedByAnyPlaceError;
use Framework\Exception\SameParamFoundInFewPlacesError;
use Framework\Exception\UnexpectedEndpointError;
use Framework\JsonCoder\JsonDecoder;
use Framework\JsonCoder\JsonEncoder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use WeakMap;

abstract class ApplicationHttpEndpointTemplate extends HttpEndpointTemplate
{
    final protected function getHttpMethod(): string
    {
        return Request::METHOD_PUT;
    }

    final public function getExpectedInput(): ExpectedInput
    {
        $endpointParams = $this->buildExpectedInput()->getEndpointParams();

        $factoryParams = [];
        foreach ($this->getServiceFactories() as $factory) {
            $factoryParams[] = $factory->buildExpectedInput()->getEndpointParams();
        }

        $allEndpointParams = array_merge($endpointParams, ...$factoryParams);

        return new ExpectedInput(...$allEndpointParams);
    }

    abstract protected function buildExpectedInput(): ExpectedInput;

    abstract protected function getServiceFactories(): EndpointServiceFactoryCollection;

    final protected function handleRequest(Request $request): Response
    {
        $parsedExpectedInput = $this->parseExpectedInput($request);
        $ignoredInput = $this->getIgnoredInput($request);
        return $this->handleInput($parsedExpectedInput, $ignoredInput);
    }

    private function parseExpectedInput(Request $request): AppliedInput
    {
        $appliedParams = new WeakMap();
        $invalidParamExceptions = [];
        foreach ($this->getExpectedInput()->getEndpointParams() as $paramSpecification) {
            try {
                $appliedParams[$paramSpecification] = $this->getAppliedParams($paramSpecification, $request);
            } catch (InvalidEndpointParamException $exception) {
                $invalidParamExceptions[] = $exception;
            }
        }

        if (!empty($invalidParamExceptions)) {
            throw new InvalidEndpointInputException(...$invalidParamExceptions);
        }

        return new AppliedInput($appliedParams);
    }

    private function getAppliedParams(
        InHttpUrlQueryAllowed|InJsonHttpBodyAllowed $paramSpecification,
        Request $request,
    ): AppliedParam
    {
        $foundParam = $this->findParamFromRequest($paramSpecification, $request);
        $paramSpecification->validateValue($foundParam);
        return new AppliedParam($foundParam);
    }

    private function findParamFromRequest(
        InHttpUrlQueryAllowed|InJsonHttpBodyAllowed $paramSpecification, 
        Request $request
    ): FoundParam
    {
        $foundParams = [];

        if ($paramSpecification instanceof InHttpUrlQueryAllowed) {
            $foundParams[] = $this->parseUrlQueryParam($paramSpecification, $request);
        }

        if ($paramSpecification instanceof InJsonHttpBodyAllowed) {
            $foundParams[] = $this->parseJsonParam($paramSpecification, $request);
        }

        switch (count($foundParams)) {
            case 0: throw new ParamIsNotAllowedByAnyPlaceError($paramSpecification);
            case 1: return $foundParams[0];
            default: throw new SameParamFoundInFewPlacesError(...$foundParams);
        }
    }

    private function parseUrlQueryParam(InHttpUrlQueryAllowed $paramSpecification, Request $request): FoundParam
    {
        $paramName = $paramSpecification->getUrlQueryParamName();
        $paramValue = $request->get($paramName, '');
        return new FoundParam($paramSpecification, ParamPlace::UrlQuery, $paramValue);
    }

    private function parseJsonParam(InJsonHttpBodyAllowed $paramSpecification, Request $request): FoundParam
    {
        $paramPath = $paramSpecification->getJsonItemPath();

        $value = (new JsonDecoder())->decode($request->getContent());
        foreach ($paramPath as $pathItem) {
            $currentHierarchyItem = $value;
            $value = is_object($currentHierarchyItem) && property_exists($currentHierarchyItem, $pathItem)
                ? $currentHierarchyItem->$pathItem
                : '';
        }

        $paramValue = is_scalar($value)
            ? (string)$value
            : (new JsonEncoder())->encode($value);

        return new FoundParam($paramSpecification, ParamPlace::JsonBody, $paramValue);
    }

    private function getIgnoredInput(Request $request): IgnoredInput
    {
        return new IgnoredInput(...array_merge(
            $this->getIgnoredUrlQueryParams($request),
            $this->getIgnoredJsonBodyParams($request),
        ));
    }

    private function getIgnoredUrlQueryParams(Request $request): array
    {
        $params = [];

        $requestQueryParams = $request->query->all();
        $expectedNamesOfUrlQueryParams = $this->getExpectedInput()->getNamesOfUrlQueryParams();
        $ignoredRequestQueryParams = array_diff_key($requestQueryParams, array_fill_keys($expectedNamesOfUrlQueryParams, null));
        foreach ($ignoredRequestQueryParams as $paramName => $paramValue) {
            $params[] = new IgnoredParam(ParamPlace::UrlQuery, $paramName, $paramValue);
        }

        return $params;
    }

    private function getIgnoredJsonBodyParams(Request $request): array
    {
        $params = [];

        try {
            $actualJson = json_decode($request->getContent(), flags: JSON_THROW_ON_ERROR);
            $expectedPathsOfJsonBodyParams = $this->getExpectedInput()->getPathsOfJsonBodyParams();

            foreach ($expectedPathsOfJsonBodyParams as $path) {
                $cursor = &$actualJson;
                $parentList = [];
                foreach ($path as $item) {
                    if (!property_exists($cursor, $item)) {
                        continue 2;
                    }
                    $parentList[] = [&$cursor, $item];
                    $cursor = &$cursor->$item;
                }
                $lastParent = array_pop($parentList);
                unset($lastParent[0]->{$lastParent[1]});

                foreach (array_reverse($parentList) as $parent) {
                    if (empty((array)$parent[0]->{$parent[1]})) {
                        unset($parent[0]->{$parent[1]});
                    }
                }
            }

            foreach ($this->getAllPaths($actualJson) as $path) {
                $params[] = new IgnoredParam(
                    ParamPlace::JsonBody,
                    $path,
                    $this->getJsonParamValue($actualJson, $path)
                );
            }
        } catch (\JsonException) {
            //
        }

        return $params;
    }

    function getAllPaths(\stdClass $jsonObject): array
    {
        $pathItems = [];
        $keys = array_keys(get_object_vars($jsonObject));
        foreach ($keys as $key) {
            $currentPath = [$key];
            if (is_object($jsonObject->$key)) {
                foreach ($this->getAllPaths($jsonObject->$key) as $nextPathItems) {
                    $pathItems[] = array_merge($currentPath, $nextPathItems);
                }
            } else {
                $pathItems[] = $currentPath;
            }
        }

        return $pathItems;
    }

    private function getJsonParamValue(object $jsonItem, array $path): string
    {
        foreach ($path as $key) {
            $jsonItem = $jsonItem->$key;
        }
        return $jsonItem;
    }

    final protected function handleInput(AppliedInput $appliedInput, IgnoredInput $ignoredInput): Response
    {
        $inputParams = $this->formatInputParams($appliedInput, $ignoredInput);

        try {
            $endpointOutput = $this->getEndpointOutput($appliedInput);
        } catch (DomainException $error) {
            throw new EndpointException($inputParams, $error);
        } catch (ExceptionWithContext|Throwable $error) {
            $failedInputParams = $this->getExpectedInput()->identifyFailedParamsByError($error);
            if (!empty($failedInputParams)) {
                throw new FailedEndpointParamError(
                    $failedInputParams,
                    $appliedInput,
                    $error,
                );
            }

            throw new UnexpectedEndpointError(
                $this->getHttpMethod(),
                $this->getHttpPath(),
                $inputParams,
                $error
            );
        }

        // Client could easily check all his send params was applied.
        // So client sure his send request was not just valid,
        // but every input param was applied as expected.
        return new JsonResponse([
            'input' => $inputParams,
            'output' => $endpointOutput,
        ]);
    }

    /**
     * @throws FailedEndpointParamError
     */
    abstract protected function executeVanguardAction(FilledExpectedInput $input): array;

    /**
     * @throws FailedEndpointParamError
     */
    abstract protected function executePostponedAction(FilledExpectedInput $input): array;

    private function formatInputParams(AppliedInput $filledInputParams, IgnoredInput $ignoredInputParams): array
    {
        $paramsWithValues = [];
        $paramsWithoutValues = [];

        foreach ($this->getExpectedInput()->getEndpointParams() as $param) {
            $appliedParam = $filledInputParams->getParam($param);

            if ($appliedParam->getValue() !== null) {
                $paramsWithValues[] = $appliedParam->formatWithValueToOutput();
            } else {
                $paramsWithoutValues[] = $appliedParam->formatWithoutValuesToOutput();
            }
        }

        return [
            'appliedExpectedParams' => $paramsWithValues,
            'unusedPossibleParams' => $paramsWithoutValues,
            'ignoredUnexpectedParams' => $ignoredInputParams->formatToOutputArray(),
        ];
    }

    private function getEndpointOutput(AppliedInput $appliedInput): array
    {
        foreach ($this->getServiceFactories() as $factory) {
            $factoryInput = $appliedInput->fillExpectedInput($factory->buildExpectedInput());
            $factory->applyInput($factoryInput);
        }

        $endpointSpecifiedInput = $appliedInput->fillExpectedInput($this->buildExpectedInput());

        return $this->isRunInDeferMode()
            ? $this->executeVanguardAction($endpointSpecifiedInput)
            : $this->executePostponedAction($endpointSpecifiedInput);
    }
}