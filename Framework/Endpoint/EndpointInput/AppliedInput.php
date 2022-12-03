<?php

namespace Framework\Endpoint\EndpointInput;

use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecificationTemplate;
use WeakMap;

final class AppliedInput
{
    /** @var WeakMap|AppliedParam[] */
    private WeakMap $params;

    public function __construct(
        WeakMap $params
    )
    {
        $this->params = $params;
    }

    public function fillExpectedInput(ExpectedInput $specifications): FilledExpectedInput
    {
        $expectedInputWithValues = new WeakMap();
        foreach ($specifications->getEndpointParams() as $paramSpecification) {
            $expectedInputWithValues[$paramSpecification] = $this->params[$paramSpecification]->getValue();
        }

        return new FilledExpectedInput($expectedInputWithValues);
    }

    /** @return AppliedParam[] */
    public function getParams(EndpointParamSpecificationTemplate ...$paramSpecifications): array
    {
        $params = [];
        foreach ($paramSpecifications as $paramSpecification) {
            $params[] = $this->params[$paramSpecification];
        }
        return $params;
    }

    /** @return AppliedParam[] */
    public function getWithoutValues(): array
    {
        $params = [];
        foreach ($this->params as $param) {
            if (!$this->hasValue($param)) {
                $params[] = $param;
            }
        }
        return $params;
    }

    /** @return AppliedParam[] */
    public function getWithValues(): array
    {
        $params = [];
        foreach ($this->params as $param) {
            if ($this->hasValue($param)) {
                $params[] = $param;
            }
        }
        return $params;
    }

    private function hasValue(mixed $param): bool
    {
        return $param->getValue() !== null && $param->getValue() !== '';
    }
}