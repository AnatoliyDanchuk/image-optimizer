<?php

namespace Framework\Exception;

use Framework\Endpoint\EndpointInput\EndpointInputInfoBuilder;
use Framework\Endpoint\EndpointInput\ParamPlace;
use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecificationTemplate;

final class ParamValueIsNotFound extends ExceptionWithContext
{
    public function __construct(EndpointParamSpecificationTemplate $param, ParamPlace $paramPlace)
    {
        parent::__construct([
            'errorReason' => 'Param value is not found.',
        ] + (new EndpointInputInfoBuilder())->buildParamPlacePathInfo($param, $paramPlace));
    }
}