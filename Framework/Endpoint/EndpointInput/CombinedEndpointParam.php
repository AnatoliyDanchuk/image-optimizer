<?php

namespace Framework\Endpoint\EndpointInput;

use Framework\Endpoint\CombinedEndpointParamSpecifications\ConvertableCombinedEndpointParamSpecifications;
use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecificationTemplate;
use WeakMap;

final class CombinedEndpointParam
{
    private WeakMap $params;

    public function __construct(
        WeakMap $params
    )
    {
        $this->params = $params;
    }

    /**
     * @param EndpointParamSpecificationTemplate|ConvertableCombinedEndpointParamSpecifications $endpointParam
     * @return mixed
     */
    public function getValue($endpointParam)
    {
        return $this->params[$endpointParam];
    }
}