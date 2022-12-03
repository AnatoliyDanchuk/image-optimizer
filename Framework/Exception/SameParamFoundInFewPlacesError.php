<?php

namespace Framework\Exception;

use Framework\Endpoint\EndpointInput\FoundParam;
use Framework\Endpoint\EndpointInput\EndpointInputInfoBuilder;

final class SameParamFoundInFewPlacesError extends ExceptionWithContext
{
    public function __construct(FoundParam ...$foundParams)
    {
        parent::__construct([
            'errorReason' => 'Expects the param found only in 1 place.',
            'foundParams' => (new EndpointInputInfoBuilder())->buildFilledInputInfo(...$foundParams),
        ]);
    }
}