<?php

namespace Framework\Endpoint\EndpointInput;

final class IgnoredInput
{
    public readonly array $params;

    public function __construct(
        IgnoredParam ...$params
    )
    {
        $this->params = $params;
    }
}