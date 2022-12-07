<?php

namespace Framework\Endpoint\EndpointInput;

use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecificationTemplate;
use Framework\Endpoint\EndpointParamSpecification\InHttpUrlQueryAllowed;
use Framework\Endpoint\EndpointParamSpecification\InJsonHttpBodyAllowed;
use Framework\Exception\ParamIsNotAllowedByAnyPlaceError;

final class EndpointInputInfoBuilder
{
    public function buildUniqueParamsInfo(array $uniqueParams): array
    {
        return [
            'uniqueParams' => array_map([$this, 'buildParamAvailablePathsInfo'], $uniqueParams),
        ];
    }

    private function buildParamAvailablePathsInfo(EndpointParamSpecificationTemplate $param): array
    {
        $info = [];

        if ($param instanceof InHttpUrlQueryAllowed) {
            /** @var EndpointParamSpecificationTemplate $param */
            $info[] = $this->buildParamPlacePathInfo($param, ParamPlace::UrlQuery);
        }
        if ($param instanceof InJsonHttpBodyAllowed) {
            $info[] = $this->buildParamPlacePathInfo($param, ParamPlace::JsonBody);
        }

        return !empty($info)
            ? $info
            : throw new ParamIsNotAllowedByAnyPlaceError($param);
    }

    public function buildParamPlacePathInfo(EndpointParamSpecificationTemplate $param, ParamPlace $paramPlace): array
    {
        /** @var InHttpUrlQueryAllowed|InJsonHttpBodyAllowed $param */
        return match ($paramPlace) {
            ParamPlace::UrlQuery => $this->buildCustomPlacePathInfo($paramPlace, $param->getUrlQueryParamName()),
            ParamPlace::JsonBody => $this->buildCustomPlacePathInfo($paramPlace, $param->getJsonItemPath()),
        };
    }

    public function buildCustomPlacePathInfo(ParamPlace $paramPlace, string|array $placePath): array
    {
        return ['paramPlace' => $paramPlace->name] + match ($paramPlace) {
            ParamPlace::UrlQuery => [
                'urlQueryParamName' => $placePath
            ],
            ParamPlace::JsonBody => [
                'jsonBodyParamPath' => implode(':{', $placePath)
            ]
        };
    }

    public function buildInputInfo(ParsedInput $parsedInput): array
    {
        return [
            'appliedExpectedParams' => $this->buildFilledInputInfo(...$parsedInput->appliedInput->getWithValues()),
            'unusedPossibleParams' => $this->buildFilledInputInfo(...$parsedInput->appliedInput->getWithoutValues()),
            'ignoredUnexpectedParams' => $this->buildIgnoredInputInfo($parsedInput->ignoredInput),
        ];
    }

    public function buildFilledInputInfo(InputParam ...$params): array
    {
        $info = [];
        foreach ($params as $param) {
            $info[] = $this->buildFilledParamInfo($param);
        }

        return $info;
    }

    public function buildFilledParamInfo(InputParam $param): array
    {
        return $this->buildCustomPlacePathInfo($param->place, $param->placePath)
            + $this->buildValueInfo($param->value);
    }

    private function buildValueInfo(mixed $value): array
    {
        return ['value' => $value];
    }

    private function buildIgnoredInputInfo(IgnoredInput $ignoredInput): array
    {
        $info = [];
        foreach ($ignoredInput->params as $ignoredParam) {
            $info[] = $this->buildFilledParamInfo($ignoredParam);
        }
        return $info;
    }
}