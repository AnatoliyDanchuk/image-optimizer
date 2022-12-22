<?php

namespace Framework\Endpoint\EndpointInput;

abstract class InputParam
{
    public function __construct(
        public readonly ParamPath $paramPath,
        public readonly string|array $value,
    )
    {
    }

    abstract public function getFormattedValue(): string|array;
}