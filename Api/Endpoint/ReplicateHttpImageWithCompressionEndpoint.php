<?php

namespace Api\Endpoint;

use Api\EndpointParamSpecification\WantedImagePathSpecification;
use Api\EndpointServiceFactory\OriginalImageHttpPathProviderFactory;
use Api\EndpointServiceFactory\S3ClientFactory;
use Api\EndpointSpecification\ReplicateImageWithCompressionSpecification;
use Domain\Action\ReplicateImageWithCompression;
use DomainAdapter\OriginalImageProvider\OriginalImageHttpProvider;
use Framework\Endpoint\EndpointInput\ExpectedInput;
use Framework\Endpoint\EndpointInput\FilledExpectedInput;
use Framework\Endpoint\EndpointTemplate\EndpointServiceFactoryCollection;

final class ReplicateHttpImageWithCompressionEndpoint extends ReplicateImageWithCompressionSpecification
{
    public function __construct(
        private readonly WantedImagePathSpecification $wantedImagePathSpecification,

        private readonly S3ClientFactory $s3ClientFactory,
        private readonly OriginalImageHttpPathProviderFactory $originalImageHttpPathProviderFactory,

        private readonly ReplicateImageWithCompression $action,
        private readonly OriginalImageHttpProvider $originalImageProvider,
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
            $this->originalImageHttpPathProviderFactory,
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