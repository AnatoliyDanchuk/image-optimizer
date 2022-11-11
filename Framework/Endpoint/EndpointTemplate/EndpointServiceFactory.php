<?php

namespace Framework\Endpoint\EndpointTemplate;

use Framework\Endpoint\EndpointInput\ExpectedInput;
use Framework\Endpoint\EndpointInput\FilledExpectedInput;

interface EndpointServiceFactory
{
    public function buildExpectedInput(): ExpectedInput;
    public function applyInput(FilledExpectedInput $input): void;
}