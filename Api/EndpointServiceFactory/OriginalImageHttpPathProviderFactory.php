<?php

namespace Api\EndpointServiceFactory;

use Api\EndpointParamSpecification\OriginalImageHttpPathSpecification;
use DomainAdapter\OriginalImagePathProvider\OriginalImageHttpPathProvider;
use Framework\Endpoint\EndpointInput\ExpectedInput;
use Framework\Endpoint\EndpointInput\FilledExpectedInput;
use Framework\Endpoint\EndpointTemplate\EndpointServiceFactory;

final class OriginalImageHttpPathProviderFactory implements EndpointServiceFactory
{
    private OriginalImageHttpPathProvider $originalImageHttpPathProvider;

    public function __construct(
        private readonly OriginalImageHttpPathSpecification $originalImageHttpPathSpecification
    )
    {
    }

    public function buildExpectedInput(): ExpectedInput
    {
        return new ExpectedInput(
            $this->originalImageHttpPathSpecification,
        );
    }

    public function applyInput(FilledExpectedInput $input): void
    {
        $this->originalImageHttpPathProvider = new OriginalImageHttpPathProvider(
            $input->getParamValue($this->originalImageHttpPathSpecification)
        );
    }

    public function __invoke(): OriginalImageHttpPathProvider
    {
        return $this->originalImageHttpPathProvider;
    }
 }