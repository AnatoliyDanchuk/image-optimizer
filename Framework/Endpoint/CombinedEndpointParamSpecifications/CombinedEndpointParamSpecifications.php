<?php

namespace Framework\Endpoint\CombinedEndpointParamSpecifications;

use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecificationTemplate;

interface CombinedEndpointParamSpecifications
{
    /** @return self|EndpointParamSpecificationTemplate[] */
    public function getOnlyChildren(): array;

    /** @return EndpointParamSpecificationTemplate[] */
    public function getAllParams(): array;
}