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

    public function getParamValue(EndpointParamSpecificationTemplate $endpointParam): mixed
    {
        return $this->params[$endpointParam];
    }

    public function getValueOfCombinedParams(ConvertableCombinedEndpointParamSpecifications $specifications): mixed
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
}