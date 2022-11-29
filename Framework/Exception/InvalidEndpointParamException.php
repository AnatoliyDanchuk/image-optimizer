<?php

namespace Framework\Exception;

use Framework\Endpoint\EndpointInput\FoundParam;

final class InvalidEndpointParamException extends ExceptionWithContext
{
    public function __construct(
        FoundParam $paramValue,
        ValidatorException $validatorException
    ) {
        parent::__construct([
            'param' => $paramValue->formatToOutput(),
            'reason' => $validatorException->getContext(),
        ], $validatorException);
    }
}