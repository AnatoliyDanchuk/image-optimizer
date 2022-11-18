<?php

namespace Api\Endpoint;

use Api\EndpointParamSpecification\WantedImagePathSpecification;
use Api\EndpointServiceFactory\OriginalImageHttpPathProviderFactory;
use Api\EndpointServiceFactory\S3ClientFactory;
use Api\CombinedEndpointParamSpecification\WantedImageGeometrySpecification;
use Api\EndpointSpecification\ReplicateImageWithResizingSpecification;
use Domain\Action\ReplicateImageWithResizing;
use DomainAdapter\OriginalImageProvider\OriginalImageHttpProvider;
use Framework\Endpoint\EndpointInput\ExpectedInput;
use Framework\Endpoint\EndpointInput\FilledExpectedInput;
use Framework\Endpoint\EndpointTemplate\EndpointServiceFactoryCollection;

final class ReplicateHttpImageWithResizingEndpoint extends ReplicateImageWithResizingSpecification
{
    public function __construct(
        private readonly WantedImagePathSpecification $wantedImagePathSpecification,
        private readonly WantedImageGeometrySpecification $wantedImageGeometrySpecification,

        private readonly S3ClientFactory $s3ClientFactory,
        private readonly OriginalImageHttpPathProviderFactory $originalImagePathProviderFactory,

        private readonly ReplicateImageWithResizing $action,
        private readonly OriginalImageHttpProvider $originalImageProvider,
    )
    {
    }

    protected function buildExpectedInput(): ExpectedInput
    {
        return new ExpectedInput(
            $this->wantedImagePathSpecification,
            $this->wantedImageGeometrySpecification,
        );
    }

    protected function getServiceFactories(): EndpointServiceFactoryCollection
    {
        return new EndpointServiceFactoryCollection(
            $this->s3ClientFactory,
            $this->originalImagePathProviderFactory,
        );
    }

    protected function replicate(FilledExpectedInput $input): void
    {
        $this->action->replicate(
            $this->originalImageProvider,
            $input->getParamValue($this->wantedImagePathSpecification),
            $input->getValueOfCombinedParams($this->wantedImageGeometrySpecification),
        );
    }
}