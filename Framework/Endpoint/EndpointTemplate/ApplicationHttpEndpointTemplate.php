<?php

namespace Framework\Endpoint\EndpointTemplate;

use Domain\Exception\DomainException;
use Framework\Endpoint\EndpointInput\ExpectedInput;
use Framework\Endpoint\EndpointInput\FilledExpectedInput;
use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecificationTemplate;
use Framework\Endpoint\EndpointParamSpecification\InHttpUrlQueryAllowed;
use Framework\Endpoint\EndpointParamSpecification\InJsonHttpBodyAllowed;
use Framework\Exception\EndpointException;
use Framework\Exception\ExceptionWithContext;
use Framework\Exception\FailedEndpointParamError;
use Framework\Exception\InvalidEndpointInputException;
use Framework\Exception\InvalidEndpointParamException;
use Framework\Exception\ParamIsNotAllowedByAnyPlaceError;
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

    private function parseExpectedInput(Request $request): FilledExpectedInput
    {
        $paramsWithValues = new WeakMap();
        $invalidParamExceptions = [];
        foreach ($this->getExpectedInput()->getEndpointParams() as $param) {
            try {
                $paramsWithValues[$param] = $this->getQueryParamValue($request, $param);
            } catch (InvalidEndpointParamException $exception) {
                $invalidParamExceptions[] = $exception;
            }
        }

        if (!empty($invalidParamExceptions)) {
            throw new InvalidEndpointInputException(...$invalidParamExceptions);
        }

        return new FilledExpectedInput($paramsWithValues);
    }

    /**
     * @return mixed
     */
    private function getQueryParamValue(
        Request $request,
        EndpointParamSpecificationTemplate $endpointParam
    )
    {
        // todo: exists in both variants
        // todo: allowed on both but exists only on 1
        // todo: Param place: JsonBody, UrlQuery, ...
        if ($endpointParam instanceof InHttpUrlQueryAllowed) {
            $paramName = $endpointParam->getUrlQueryParamName();
            $paramValue = $request->get($paramName, '');
        } elseif ($endpointParam instanceof InJsonHttpBodyAllowed) {
            $paramPath = $endpointParam->getJsonItemPath();

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
        } else {
            throw new ParamIsNotAllowedByAnyPlaceError($endpointParam);
        }
        $endpointParam->validateValue($paramValue);
        return $endpointParam->parseValue($paramValue);
    }

    private function getIgnoredInput(Request $request): array
    {
        $requestQueryParams = $request->query->all();
        $expectedNamesOfParams = $this->getExpectedInput()->getNamesOfAllParams();
        return array_diff_key($requestQueryParams, array_fill_keys($expectedNamesOfParams, null));
    }

    final protected function handleInput(FilledExpectedInput $filledInput, array $ignoredInput): Response
    {
        $inputParams = $this->formatInputParams($filledInput, $ignoredInput);

        try {
            $endpointOutput = $this->getEndpointOutput($filledInput);
        } catch (DomainException $error) {
            throw new EndpointException($inputParams, $error);
        } catch (ExceptionWithContext|Throwable $error) {
            $failedInputParams = $this->getExpectedInput()->identifyFailedParamsByError($error);
            if (!empty($failedInputParams)) {
                throw new FailedEndpointParamError(
                    $failedInputParams,
                    $filledInput,
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

    private function formatInputParams(FilledExpectedInput $filledInputParams, array $ignoredInputParams): array
    {
        $paramsWithValues = [];
        $paramsWithoutValues = [];

        foreach ($this->getExpectedInput()->getEndpointParams() as $param) {
            $paramValue = $filledInputParams->getParamValue($param);

            // todo: exists in both variants
            // todo: allowed on both but exists only on 1
            // todo: Param place: JsonBody, UrlQuery, ...
            if ($param instanceof InHttpUrlQueryAllowed) {
                $paramName = $param->getUrlQueryParamName();
            } elseif ($param instanceof InJsonHttpBodyAllowed) {
                $paramName = implode(':{', $param->getJsonItemPath());
            }
            if ($paramValue !== null) {
                $paramsWithValues[$paramName] = $paramValue;
            } else {
                $paramsWithoutValues[] = $paramName;
            }
        }

        return [
            'appliedExpectedParams' => $paramsWithValues,
            'unusedPossibleParams' => $paramsWithoutValues,
            'ignoredUnexpectedParams' => $ignoredInputParams,
        ];
    }

    private function getEndpointOutput(FilledExpectedInput $filledInput): array
    {
        foreach ($this->getServiceFactories() as $factory) {
            $factoryInput = $filledInput->extract($factory->buildExpectedInput());
            $factory->applyInput($factoryInput);
        }

        $endpointSpecifiedInput = $filledInput->extract($this->buildExpectedInput());

        return $this->isRunInDeferMode()
            ? $this->executeVanguardAction($endpointSpecifiedInput)
            : $this->executePostponedAction($endpointSpecifiedInput);
    }
}