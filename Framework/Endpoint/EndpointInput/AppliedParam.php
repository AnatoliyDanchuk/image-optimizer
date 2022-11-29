<?php

namespace Framework\Endpoint\EndpointInput;

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

    public function formatWithValueToOutput(): array
    {
        return $this->formatPlace() + $this->formatPlaceDetails() + $this->formatValue();
    }

    public function formatWithoutValuesToOutput(): array
    {
        return $this->formatPlace() + $this->formatPlaceDetails();
    }

    private function formatPlace(): array
    {
        return ['place' => $this->foundParam->place->name];
    }

    private function formatPlaceDetails(): array
    {
        return match ($this->foundParam->place) {
            ParamPlace::UrlQuery => ['name' => $this->foundParam->specification->getUrlQueryParamName()],
            ParamPlace::JsonBody => ['path' => implode(':{', $this->foundParam->specification->getJsonItemPath())],
        };
    }

    private function formatValue(): array
    {
        return ['value' => $this->parsedValue];
    }
}