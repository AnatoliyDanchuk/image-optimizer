<?php

namespace Framework\Exception;

use Framework\Endpoint\EndpointInput\FoundParam;
use Framework\Endpoint\EndpointInput\EndpointInputInfoBuilder;

final class InvalidEndpointParamException extends ExceptionWithContext
{
    public function __construct(
        FoundParam $foundParam,
        ValidatorException $validatorException
    ) {
        parent::__construct([
            'foundParam' => (new EndpointInputInfoBuilder())->buildFilledParamInfo($foundParam),
            'reason' => $validatorException->getContext(),
        ], $validatorException);
    }
}