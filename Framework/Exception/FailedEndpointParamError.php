<?php

namespace Framework\Exception;

use Framework\Endpoint\EndpointInput\FilledExpectedInput;
use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecificationTemplate;
use Framework\Endpoint\EndpointParamSpecification\InHttpUrlQueryAllowed;
use Framework\Endpoint\EndpointParamSpecification\InJsonHttpBodyAllowed;

final class FailedEndpointParamError extends ExceptionWithContext
{
    /** @param EndpointParamSpecificationTemplate[] $params */
    public function __construct(
        array $params,
        FilledExpectedInput $filledInput,
        ExceptionWithContext $exception
    ) {
        $relatedParametersInfo = [];
        foreach ($params as $param) {
            // todo: exists in both variants
            // todo: allowed on both but exists only on 1
            // todo: Param place: JsonBody, UrlQuery, ...
            if ($param instanceof InHttpUrlQueryAllowed) {
                $paramName = $param->getUrlQueryParamName();
            } elseif ($param instanceof InJsonHttpBodyAllowed) {
                $paramName = implode(':{', $param->getJsonItemPath());
            }
            $relatedParametersInfo[] = [
                'param' => [
                    'name' => $paramName,
                    'value' => $filledInput->getParamValue($param),
                ],
            ];
        }

        $reason = $exception->getContext();

        $context = [
            'failedInput' => [
                'relatedParameters' => $relatedParametersInfo,
                'reason' => $reason
            ],
        ];

        parent::__construct($context, $exception);
    }
}