<?php

namespace Api\EndpointServiceFactory;

use Api\EndpointParamSpecification\OriginalImageS3PathSpecification;
use DomainAdapter\OriginalImagePathProvider\OriginalImageS3PathProvider;
use Framework\Endpoint\EndpointInput\ExpectedInput;
use Framework\Endpoint\EndpointInput\FilledExpectedInput;
use Framework\Endpoint\EndpointTemplate\EndpointServiceFactory;

final class OriginalImageS3PathProviderFactory implements EndpointServiceFactory
{
    private OriginalImageS3PathProvider $originalImageS3PathProvider;

    public function __construct(
        private readonly OriginalImageS3PathSpecification $originalImageS3PathSpecification
    )
    {
    }

    public function buildExpectedInput(): ExpectedInput
    {
        return new ExpectedInput(
            $this->originalImageS3PathSpecification,
        );
    }

    public function applyInput(FilledExpectedInput $input): void
    {
        $this->originalImageS3PathProvider = new OriginalImageS3PathProvider(
            $input->getParamValue($this->originalImageS3PathSpecification)
        );
    }

    public function __invoke(): OriginalImageS3PathProvider
    {
        return $this->originalImageS3PathProvider;
    }
 }