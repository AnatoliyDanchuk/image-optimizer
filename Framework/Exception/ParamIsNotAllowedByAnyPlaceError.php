<?php

namespace Framework\Exception;

use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecificationTemplate;
use Framework\Endpoint\EndpointParamSpecification\InHttpUrlQueryAllowed;
use Framework\Endpoint\EndpointParamSpecification\InJsonHttpBodyAllowed;

final class ParamIsNotAllowedByAnyPlaceError extends ExceptionWithContext
{
    public function __construct(EndpointParamSpecificationTemplate $param)
    {
        parent::__construct([
            'uncompletedParamClass' => get_class($param),
            'errorReason' => 'Expects at least one allowed way(Interface) for fill param with value.',
            'allowed ways (Interfaces)' => [
                InHttpUrlQueryAllowed::class,
                InJsonHttpBodyAllowed::class,
            ]
        ]);
    }
}