<?php

namespace Framework\Endpoint\EndpointInput;

use Framework\Endpoint\CombinedEndpointParamSpecifications\CombinedEndpointParamSpecifications;
use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecificationTemplate;
use Framework\Endpoint\EndpointParamSpecification\HasRelatedErrorClass;
use Framework\Endpoint\EndpointParamSpecification\InHttpUrlQueryAllowed;
use Framework\Endpoint\EndpointParamSpecification\InJsonHttpBodyAllowed;
use Framework\Exception\ParamIsNotAllowedByAnyPlaceError;
use Throwable;

final class ExpectedInput
{
    /** @var EndpointParamSpecificationTemplate[] */
    private array $endpointParams;

    /**
     * @param CombinedEndpointParamSpecifications|EndpointParamSpecificationTemplate ...$endpointParams
     */
    public function __construct(...$endpointParams)
    {
        $combinedParams = [];
        $separatedParams = [];
        foreach ($endpointParams as $item) {
            if ($item instanceof CombinedEndpointParamSpecifications) {
                $combinedParams[] = $item->getAllParams();
            } else {
                $separatedParams[] = $item;
            }
        }

        $this->endpointParams = array_merge($separatedParams, ...$combinedParams);
    }

    public function getEndpointParams(): array
    {
        return $this->endpointParams;
    }

    public function getNamesOfAllParams(): array
    {
        $names = [];
        foreach ($this->endpointParams as $param) {
            // todo: exists in both variants
            // todo: allowed on both but exists only on 1
            // todo: Param place: JsonBody, UrlQuery, ...
            if ($param instanceof InHttpUrlQueryAllowed) {
                $paramName = $param->getUrlQueryParamName();
            } elseif ($param instanceof InJsonHttpBodyAllowed) {
                $paramName = implode(':{', $param->getJsonItemPath());
            } else {
                throw new ParamIsNotAllowedByAnyPlaceError($param);
            }
            $names[] = $paramName;
        }

        return $names;
    }

    public function identifyFailedParamsByError(Throwable $error): array
    {
        $params = [];
        foreach ($this->endpointParams as $param) {
            if ($param instanceof HasRelatedErrorClass
                && $param->getRelatedErrorClasses()->containsError($error)
            ) {
                $params[] = $param;
            }
        }

        return $params;
    }
}