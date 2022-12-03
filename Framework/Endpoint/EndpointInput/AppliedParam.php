<?php

namespace Framework\Endpoint\EndpointInput;

use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecificationTemplate;

final class AppliedParam
{
    private mixed $parsedValue;

    public function __construct(
        private readonly FoundParam $foundParam,
    )
    {
        $this->parsedValue = $this->foundParam->specification->parseValue($this->foundParam->value);
    }

    public function getValue(): mixed
    {
        return $this->parsedValue;
    }

    public function getParamSpecification(): EndpointParamSpecificationTemplate
    {
        return $this->foundParam->specification;
    }

    public function getPlaceFoundIn(): ParamPlace
    {
        return $this->foundParam->place;
    }
}