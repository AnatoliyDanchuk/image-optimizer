<?php

namespace Api\EndpointServiceFactory;

use Api\EndpointParamSpecification\ProvidedImageUrlSpecification;
use DomainAdapter\OriginalImagePathProvider\ProvidedImageUrlProvider;
use Framework\Endpoint\EndpointInput\ExpectedInput;
use Framework\Endpoint\EndpointInput\FilledExpectedInput;
use Framework\Endpoint\EndpointTemplate\EndpointServiceFactory;

final class ProvidedImageUrlProviderFactory implements EndpointServiceFactory
{
    private ProvidedImageUrlProvider $providedImageUrlProvider;

    public function __construct(
        private readonly ProvidedImageUrlSpecification $providedImageUrlSpecification
    )
    {
    }

    public function buildExpectedInput(): ExpectedInput
    {
        return new ExpectedInput(
            $this->providedImageUrlSpecification,
        );
    }

    public function applyInput(FilledExpectedInput $input): void
    {
        $this->providedImageUrlProvider = new ProvidedImageUrlProvider(
            $input->getParamValue($this->providedImageUrlSpecification)
        );
    }

    public function __invoke(): ProvidedImageUrlProvider
    {
        return $this->providedImageUrlProvider;
    }
 }