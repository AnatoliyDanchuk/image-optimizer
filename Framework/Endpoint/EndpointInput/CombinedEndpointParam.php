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

    public function getValue(
        EndpointParamSpecificationTemplate|ConvertableCombinedEndpointParamSpecifications $endpointParam
    ): mixed
    {
        return $this->params[$endpointParam];
    }
}