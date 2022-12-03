<?php

namespace Framework\Endpoint\EndpointInput;

final class IgnoredParam
{
    public function __construct(
        public readonly ParamPlace $place,
        public readonly string|array $detailedPlace,
        public readonly string $value,
    )
    {
    }
}