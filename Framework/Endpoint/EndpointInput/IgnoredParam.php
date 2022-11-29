<?php

namespace Framework\Endpoint\EndpointInput;

final class IgnoredParam
{
    public function __construct(
        private ParamPlace $place,
        private string|array $detailedPlace,
        private string $value,
    )
    {
    }

    public function formatWithValueToOutput(): array
    {
        return $this->formatPlace() + $this->formatPlaceDetails() + $this->formatValue();
    }

    private function formatPlace(): array
    {
        return ['place' => $this->place->name];
    }

    private function formatPlaceDetails(): array
    {
        return match ($this->place) {
            ParamPlace::UrlQuery => ['name' => $this->detailedPlace],
            ParamPlace::JsonBody => ['path' => implode(':{', $this->detailedPlace)],
        };
    }

    private function formatValue(): array
    {
        return ['value' => $this->value];
    }
}