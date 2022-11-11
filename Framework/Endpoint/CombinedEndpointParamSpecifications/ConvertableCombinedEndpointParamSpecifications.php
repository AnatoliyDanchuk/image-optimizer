<?php

namespace Framework\Endpoint\CombinedEndpointParamSpecifications;

use Framework\Endpoint\EndpointInput\CombinedEndpointParam;

interface ConvertableCombinedEndpointParamSpecifications
    extends CombinedEndpointParamSpecifications
{
    /**
     * @return mixed
     */
    public function toApplicationObject(CombinedEndpointParam $combinedEndpointParam);
}