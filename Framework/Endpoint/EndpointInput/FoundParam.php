<?php

namespace Framework\Endpoint\EndpointInput;

use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecificationTemplate;
use Framework\Endpoint\EndpointParamSpecification\InHttpUrlQueryAllowed;
use Framework\Endpoint\EndpointParamSpecification\InJsonHttpBodyAllowed;

final class FoundParam
{
    public function __construct(
        public readonly EndpointParamSpecificationTemplate|InHttpUrlQueryAllowed|InJsonHttpBodyAllowed $specification,
        public readonly ParamPlace $place,
        public readonly mixed $value,
    )
    {
    }

    public function formatToOutput(): array
    {
        return [
            'place' => $this->place,
            'path' => match ($this->place) {
                ParamPlace::UrlQuery => $this->specification->getUrlQueryParamName(),
                ParamPlace::JsonBody => implode(':{', $this->specification->getJsonItemPath()),
            },
            'foundValue' => $this->value,
        ];
    }
}