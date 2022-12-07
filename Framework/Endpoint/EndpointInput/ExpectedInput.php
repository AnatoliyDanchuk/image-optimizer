<?php

namespace Framework\Endpoint\EndpointInput;

use Framework\Endpoint\CombinedEndpointParamSpecifications\CombinedEndpointParamSpecifications;
use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecificationTemplate;
use Framework\Endpoint\EndpointParamSpecification\HasRelatedErrorClass;
use Throwable;

final class ExpectedInput
{
    /** @var EndpointParamSpecificationTemplate[] */
    private array $endpointParams;

    public function __construct(CombinedEndpointParamSpecifications|EndpointParamSpecificationTemplate ...$endpointParams)
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