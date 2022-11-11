<?php

namespace Api\Endpoint;

use Api\EndpointParamSpecification\WantedImagePathSpecification;
use Api\EndpointServiceFactory\OriginalImageS3PathProviderFactory;
use Api\EndpointServiceFactory\S3ClientFactory;
use Api\EndpointSpecification\ReplicateImageWithCompressionSpecification;
use Domain\Action\ReplicateImageWithCompression;
use DomainAdapter\OriginalImageProvider\OriginalImageS3Provider;
use Framework\Endpoint\EndpointInput\ExpectedInput;
use Framework\Endpoint\EndpointInput\FilledExpectedInput;
use Framework\Endpoint\EndpointTemplate\EndpointServiceFactoryCollection;

final class ReplicateS3ImageWithCompressionEndpoint extends ReplicateImageWithCompressionSpecification
{
    public function __construct(
        private readonly WantedImagePathSpecification $wantedImagePathSpecification,

        private readonly S3ClientFactory $s3ClientFactory,
        private readonly OriginalImageS3PathProviderFactory $originalImageS3PathProviderFactory,

        private readonly ReplicateImageWithCompression $action,
        private readonly OriginalImageS3Provider $originalImageProvider,
    )
    {
    }

    protected function buildExpectedInput(): ExpectedInput
    {
        return new ExpectedInput(
            $this->wantedImagePathSpecification,
        );
    }

    protected function getServiceFactories(): EndpointServiceFactoryCollection
    {
        return new EndpointServiceFactoryCollection(
            $this->s3ClientFactory,
            $this->originalImageS3PathProviderFactory,
        );
    }

    protected function replicate(FilledExpectedInput $input): void
    {
        $this->action->replicate(
            $this->originalImageProvider,
            $input->getParamValue($this->wantedImagePathSpecification),
        );
    }
}