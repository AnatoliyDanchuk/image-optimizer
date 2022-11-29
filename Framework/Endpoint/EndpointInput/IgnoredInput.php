<?php

namespace Framework\Endpoint\EndpointInput;

final class IgnoredInput
{
    private array $params;

    public function __construct(
        IgnoredParam ...$params
    )
    {
        $this->params = $params;
    }

    public function formatToOutputArray(): array
    {
        $formatted = [];
        foreach ($this->params as $param) {
            $formatted[] = $param->formatWithValueToOutput();
        }

        return $formatted;
    }
}