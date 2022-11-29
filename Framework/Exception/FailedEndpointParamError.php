<?php

namespace Framework\Exception;

use Framework\Endpoint\EndpointInput\AppliedInput;
use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecificationTemplate;

final class FailedEndpointParamError extends ExceptionWithContext
{
    /** @param EndpointParamSpecificationTemplate[] $params */
    public function __construct(
        array $params,
        AppliedInput $appliedInput,
        ExceptionWithContext $exception
    ) {
        $relatedParametersInfo = [];
        foreach ($params as $param) {
            $relatedParametersInfo[] = $appliedInput->getParam($param)->formatWithValueToOutput();
        }

        parent::__construct([
            'failedInput' => [
                'relatedParameters' => $relatedParametersInfo,
                'reason' => $exception->getContext()
            ],
        ], $exception);
    }
}