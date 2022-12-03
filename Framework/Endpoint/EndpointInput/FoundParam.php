<?php

namespace Framework\Endpoint\EndpointInput;

use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecificationTemplate;
use Framework\Endpoint\EndpointParamSpecification\InHttpUrlQueryAllowed;
use Framework\Endpoint\EndpointParamSpecification\InJsonHttpBodyAllowed;

final class FoundParam
{
    public function __construct(
        public readonly EndpointParamSpecificationTemplate|InHttpUrlQueryAllowed|InJsonHttpBodyAllowed $specification,
        public readonly ParamPlace $place,
        public readonly mixed $value,
    )
    {
    }
}