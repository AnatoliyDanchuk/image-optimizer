<?php

namespace Framework\Endpoint\EndpointInput;

use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecificationTemplate;

final class AppliedParam extends InputParam
{
    private readonly string|array $foundValue;

    public function __construct(
        FoundInputParam $foundInputParam,
        EndpointParamSpecificationTemplate $paramSpecification
    )
    {
        $this->foundValue = $foundInputParam->value;
        parent::__construct($foundInputParam->paramPath, $paramSpecification->parseValue($foundInputParam->value));
    }

    public function getFormattedValue(): string|array
    {
        return $this->foundValue;
    }
}