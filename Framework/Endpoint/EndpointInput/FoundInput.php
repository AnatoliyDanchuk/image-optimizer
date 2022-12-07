<?php

namespace Framework\Endpoint\EndpointInput;

use Framework\Exception\ParamValueIsNotFound;

final class FoundInput
{
    private array $indexedParams;

    public function __construct(
        FoundInputParam ...$params
    )
    {
        $this->indexedParams = [];
        foreach ($params as $param) {
            $this->indexedParams[$this->buildIndexKey($param->place, $param->placePath)] = $param;
        }
    }

    private function buildIndexKey(ParamPlace $searchParamPlace, array|string $searchPlaceDetails): string
    {
        return $searchParamPlace->name . '_' . serialize($searchPlaceDetails);
    }

    public function getParam(ParamPlace $searchParamPlace, array|string $searchPlaceDetails): FoundInputParam
    {
        $key = $this->buildIndexKey($searchParamPlace, $searchPlaceDetails);
        return $this->indexedParams[$key] ?? throw new ParamValueIsNotFound($searchParamPlace, $searchPlaceDetails);
    }

    public function diff(FoundInputParam ...$params): array
    {
        /** @see FoundInputParam::__toString() */
        return array_values(array_diff($this->indexedParams, $params));
    }
}