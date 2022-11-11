<?php

namespace Framework\Exception;

use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecificationTemplate;
use Framework\Endpoint\EndpointParamSpecification\InHttpUrlQueryAllowed;
use Framework\Endpoint\EndpointParamSpecification\InJsonHttpBodyAllowed;

final class InvalidEndpointParamException extends ExceptionWithContext
{
    /**
     * @param mixed $paramValue
     */
    public function __construct(
        EndpointParamSpecificationTemplate $param,
        $paramValue,
        ValidatorException $validatorException
    ) {
        // todo: exists in both variants
        // todo: allowed on both but exists only on 1
        // todo: Param place: JsonBody, UrlQuery, ...
        if ($param instanceof InHttpUrlQueryAllowed) {
            $paramName = $param->getUrlQueryParamName();
        } elseif ($param instanceof InJsonHttpBodyAllowed) {
            $paramName = implode(':{', $param->getJsonItemPath());
        }

        $paramInfo = [
            'param' => [
                'name' =>$paramName,
                'value' => $paramValue,
            ]
        ];
        $reason = [
            'reason' => $validatorException->getContext(),
        ];
        $context = array_merge($paramInfo, $reason);

        parent::__construct($context, $validatorException);
    }
}