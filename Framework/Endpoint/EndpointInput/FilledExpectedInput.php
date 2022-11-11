<?php

namespace Framework\Endpoint\EndpointInput;

use Framework\Endpoint\CombinedEndpointParamSpecifications\ConvertableCombinedEndpointParamSpecifications;
use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecificationTemplate;
use WeakMap;

final class FilledExpectedInput
{
    private WeakMap $params;

    public function __construct(
        WeakMap $params
    )
    {
        $this->params = $params;
    }

    /**
     * @return mixed
     */
    public function getParamValue(EndpointParamSpecificationTemplate $endpointParam)
    {
        return $this->params[$endpointParam];
    }

    /**
     * @return mixed
     */
    public function getValueOfCombinedParams(ConvertableCombinedEndpointParamSpecifications $specifications)
    {
        $groupParamsWithValues = new WeakMap();
        foreach ($specifications->getOnlyChildren() as $item) {
            if ($item instanceof ConvertableCombinedEndpointParamSpecifications) {
                $groupParamsWithValues[$item] = $this->getValueOfCombinedParams($item);
            } else {
                $groupParamsWithValues[$item] = $this->params[$item];
            }
        }

        return $specifications->toApplicationObject(new CombinedEndpointParam($groupParamsWithValues));
    }

    public function extract(ExpectedInput $specifications): self
    {
        $expectedInputWithValues = new WeakMap();
        foreach ($specifications->getEndpointParams() as $paramSpecification) {
            $expectedInputWithValues[$paramSpecification] = $this->params[$paramSpecification];
        }

        return new self($expectedInputWithValues);
    }
}