<?php

namespace Framework\Exception;

use Framework\Endpoint\EndpointInput\EndpointInputInfoBuilder;
use Framework\Endpoint\EndpointInput\ParamPlace;

final class ParamValueIsNotFound extends ExceptionWithContext
{
    public function __construct(ParamPlace $paramPlace, string|array $paramDetailedPath)
    {
        parent::__construct([
            'errorReason' => 'Param value is not found.',
        ] + (new EndpointInputInfoBuilder())->buildCustomPlacePathInfo($paramPlace, $paramDetailedPath));
    }
}