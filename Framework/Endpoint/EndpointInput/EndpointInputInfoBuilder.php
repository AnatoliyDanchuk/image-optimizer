<?php

namespace Framework\Endpoint\EndpointInput;

use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecificationTemplate;
use Framework\Endpoint\EndpointParamSpecification\InHttpUrlQueryAllowed;
use Framework\Endpoint\EndpointParamSpecification\InJsonHttpBodyAllowed;
use Framework\Exception\ParamIsNotAllowedByAnyPlaceError;

final class EndpointInputInfoBuilder
{
    public function buildParamPathsInfo(EndpointParamSpecificationTemplate ...$uniqueParams): array
    {
        $info = [];
        foreach ($uniqueParams as $param) {
            $info[] = $this->buildParamAvailablePathsInfo($param);
        }
        return $info;
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

    public function buildCustomPlacePathInfo(ParamPlace $paramPlace, string|array $paramPlaceDetails): array
    {
        return ['paramPlace' => $paramPlace->name] + match ($paramPlace) {
            ParamPlace::UrlQuery => [
                'urlQueryParamName' => $paramPlaceDetails
            ],
            ParamPlace::JsonBody => [
                'jsonBodyParamPath' => implode(':{', $paramPlaceDetails)
            ]
        };
    }

    public function buildInputInfo(AppliedInput $appliedInput, IgnoredInput $ignoredInput): array
    {
        return [
            'appliedExpectedParams' => $this->buildFilledInputInfo(...$appliedInput->getWithValues()),
            'unusedPossibleParams' => $this->buildFilledInputInfo(...$appliedInput->getWithoutValues()),
            'ignoredUnexpectedParams' => $this->buildIgnoredInputInfo($ignoredInput),
        ];
    }

    public function buildFilledInputInfo(AppliedParam|FoundParam ...$params): array
    {
        $info = [];
        foreach ($params as $param) {
            $info[] = $this->buildFilledParamInfo($param);
        }

        return $info;
    }

    public function buildFilledParamInfo(AppliedParam|FoundParam $param): array
    {
        return $this->buildParamPlacePathInfo($param->getParamSpecification(), $param->getPlaceFoundIn())
            + $this->buildValueInfo($param->getValue());
    }

    private function buildValueInfo(mixed $value): array
    {
        return ['value' => $value];
    }

    private function buildIgnoredInputInfo(IgnoredInput $ignoredInput): array
    {
        $info = [];
        foreach ($ignoredInput->params as $ignoredParam) {
            $info[] = $this->buildCustomFilledParamInfo($ignoredParam);
        }
        return $info;
    }

    private function buildCustomFilledParamInfo(IgnoredParam $ignoredParam): array
    {
        return $this->buildCustomPlacePathInfo($ignoredParam->place, $ignoredParam->detailedPlace)
            + $this->buildValueInfo($ignoredParam->value);
    }
}