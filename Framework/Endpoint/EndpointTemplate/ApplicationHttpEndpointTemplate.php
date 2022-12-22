<?php

namespace Framework\Endpoint\EndpointTemplate;

use Domain\Exception\DomainException;
use Framework\Endpoint\EndpointInput\AppliedInput;
use Framework\Endpoint\EndpointInput\AppliedParam;
use Framework\Endpoint\EndpointInput\ExpectedInput;
use Framework\Endpoint\EndpointInput\FilledExpectedInput;
use Framework\Endpoint\EndpointInput\IgnoredInput;
use Framework\Endpoint\EndpointInput\JsonBodyParamPath;
use Framework\Endpoint\EndpointInput\ParsedInput;
use Framework\Endpoint\EndpointInput\FoundInput;
use Framework\Endpoint\EndpointInput\FoundInputParam;
use Framework\Endpoint\EndpointInput\UrlQueryParamPath;
use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecificationTemplate;
use Framework\Exception\EndpointException;
use Framework\Exception\ExceptionWithContext;
use Framework\Exception\FailedEndpointParamError;
use Framework\Exception\InvalidEndpointInputException;
use Framework\Exception\InvalidEndpointParamException;
use Framework\Exception\ParamValueIsNotFoundAnywhere;
use Framework\Exception\SameParamFoundInFewPlacesError;
use Framework\Exception\UnexpectedEndpointError;
use Framework\Exception\ParamValueIsNotFound;
use Framework\Exception\ValidatorException;
use Framework\Endpoint\EndpointInput\EndpointInputInfoBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use WeakMap;

abstract class ApplicationHttpEndpointTemplate extends HttpEndpointTemplate
{
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
        $foundInput = new FoundInput(...array_merge(
            $this->getFoundUrlQueryParams($request),
            $this->getFoundJsonBodyParams($request),
        ));
        $parsedInput = $this->parseInput($foundInput);
        return $this->handleInput($parsedInput);
    }

    private function parseInput(FoundInput $foundInput): ParsedInput
    {
        $appliedParams = new WeakMap();
        $expectedFoundParams = [];
        $invalidParamExceptions = [];

        foreach ($this->getExpectedInput()->getEndpointParams() as $paramSpecification) {
            $foundParam = $this->getFoundParam($paramSpecification, $foundInput);
            try {
                $appliedParams[$paramSpecification] = $this->buildAppliedParam($paramSpecification, $foundParam);
            } catch (InvalidEndpointParamException $exception) {
                $invalidParamExceptions[] = $exception;
            }
            $expectedFoundParams[] = $foundParam;
        }

        if (!empty($invalidParamExceptions)) {
            throw new InvalidEndpointInputException(...$invalidParamExceptions);
        }

        return new ParsedInput(
            new AppliedInput($appliedParams),
            new IgnoredInput(...$foundInput->diff(...$expectedFoundParams)),
        );
    }

    private function buildAppliedParam(
        EndpointParamSpecificationTemplate $paramSpecification,
        FoundInputParam $foundInputParam,
    ): AppliedParam
    {
        try {
            $paramSpecification->validateValue($foundInputParam->value);
        } catch (ValidatorException $exception) {
            throw new InvalidEndpointParamException($foundInputParam, $exception);
        }
        return new AppliedParam(
            $foundInputParam,
            $paramSpecification,
        );
    }

    private function getFoundParam(
        EndpointParamSpecificationTemplate $paramSpecification,
        FoundInput $foundInput,
    ): FoundInputParam
    {
        $foundParams = [];
        $notFoundExceptions = [];

        foreach ($paramSpecification->getAvailableParamPaths()->paramPaths as $paramPath) {
            try {
                $foundParams[] = $foundInput->getParam($paramPath);
            } catch (ParamValueIsNotFound $exception) {
                $notFoundExceptions[] = $exception;
            }
        }

        switch (count($foundParams)) {
            case 0: throw new ParamValueIsNotFoundAnywhere(...$notFoundExceptions);
            case 1: return $foundParams[0];
            default: throw new SameParamFoundInFewPlacesError(...$foundParams);
        }
    }

    private function getFoundUrlQueryParams(Request $request): array
    {
        $params = [];

        foreach ($request->query->all() as $paramName => $paramValue) {
            $params[] = new FoundInputParam(
                new UrlQueryParamPath($paramName),
                $paramValue,
            );
        }

        return $params;
    }

    private function getFoundJsonBodyParams(Request $request): array
    {
        $params = [];

        try {
            $foundJson = json_decode($request->getContent(), flags: JSON_THROW_ON_ERROR);
            foreach ($this->getAllPaths($foundJson) as $path) {
                $params[] = new FoundInputParam(
                    new JsonBodyParamPath($path),
                    $this->getJsonParamValue($foundJson, $path),
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

    private function getJsonParamValue(object $jsonItem, array $path): string|array
    {
        foreach ($path as $key) {
            $jsonItem = $jsonItem->$key;
        }
        return $jsonItem;
    }

    final protected function handleInput(ParsedInput $parsedInput): Response
    {
        $inputInfo = (new EndpointInputInfoBuilder())->buildInputInfo($parsedInput);

        try {
            $endpointOutput = $this->getEndpointOutput($parsedInput->appliedInput);
        } catch (DomainException $error) {
            throw new EndpointException($inputInfo, $error);
        } catch (ExceptionWithContext|Throwable $error) {
            $failedInputParams = $this->getExpectedInput()->identifyFailedParamsByError($error);
            if (!empty($failedInputParams)) {
                throw new FailedEndpointParamError(
                    $error,
                    ...$parsedInput->appliedInput->getParams(...$failedInputParams),
                );
            }

            throw new UnexpectedEndpointError(
                $this->getHttpMethod(),
                $this->getHttpPath(),
                $inputInfo,
                $error
            );
        }

        // Client could easily check all his send params was applied.
        // So client sure his send request was not just valid,
        // but every input param was applied as expected.
        return new JsonResponse([
            'input' => $inputInfo,
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