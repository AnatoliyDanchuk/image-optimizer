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

    public function getParam(EndpointParamSpecificationTemplate $endpointParam): AppliedParam
    {
        return $this->params[$endpointParam];
    }

    public function fillExpectedInput(ExpectedInput $specifications): FilledExpectedInput
    {
        $expectedInputWithValues = new WeakMap();
        foreach ($specifications->getEndpointParams() as $paramSpecification) {
            $expectedInputWithValues[$paramSpecification] = $this->params[$paramSpecification]->getValue();
        }

        return new FilledExpectedInput($expectedInputWithValues);
    }
}