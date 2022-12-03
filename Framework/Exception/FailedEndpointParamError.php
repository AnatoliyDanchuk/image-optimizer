<?php

namespace Framework\Exception;

use Framework\Endpoint\EndpointInput\AppliedParam;
use Framework\Endpoint\EndpointInput\EndpointInputInfoBuilder;

final class FailedEndpointParamError extends ExceptionWithContext
{
    public function __construct(
        ExceptionWithContext $exception,
        AppliedParam ...$appliedInput,
    ) {
        parent::__construct([
            'failedInput' => [
                'relatedParameters' => (new EndpointInputInfoBuilder())->buildFilledInputInfo(...$appliedInput),
                'reason' => $exception->getContext()
            ],
        ], $exception);
    }
}