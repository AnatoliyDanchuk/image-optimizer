<?php

namespace Framework\Endpoint\EndpointInput;

abstract class InputParam
{
    public function __construct(
        public readonly ParamPlace $place,
        public readonly string|array $placePath,
        public readonly string $value,
    )
    {
    }
}