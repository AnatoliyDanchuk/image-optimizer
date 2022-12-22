<?php

namespace Api\Endpoint;

use Api\EndpointParamSpecification\WantedReplicationSpecification;
use Api\EndpointServiceFactory\InstanceIdProviderFactory;
use Api\EndpointServiceFactory\ProvidedImageUrlProviderFactory;
use Api\EndpointServiceFactory\S3ClientFactory;
use Api\EndpointServiceFactory\SupplierIdProviderFactory;
use Api\EndpointSpecification\ReplicateImageWithResizingSpecification;
use Domain\Action\ReplicateImageWithResizing;
use DomainAdapter\OriginalImageProvider\OriginalImageHttpProvider;
use Framework\Endpoint\EndpointInput\ExpectedInput;
use Framework\Endpoint\EndpointInput\FilledExpectedInput;
use Framework\Endpoint\EndpointTemplate\EndpointServiceFactoryCollection;

final class ReplicateHttpImageWithResizingEndpoint extends ReplicateImageWithResizingSpecification
{
    public function __construct(
        private readonly WantedReplicationSpecification $wantedReplicationSpecification,

        private readonly S3ClientFactory $s3ClientFactory,
        private readonly ProvidedImageUrlProviderFactory $providedImageUrlProviderFactory,
        private readonly InstanceIdProviderFactory $instanceIdProviderFactory,
        private readonly SupplierIdProviderFactory $supplierIdProviderFactory,

        private readonly ReplicateImageWithResizing $action,
        private readonly OriginalImageHttpProvider $originalImageProvider,
    )
    {
    }

    protected function buildExpectedInput(): ExpectedInput
    {
        return new ExpectedInput(
            $this->wantedReplicationSpecification,
        );
    }

    protected function getServiceFactories(): EndpointServiceFactoryCollection
    {
        return new EndpointServiceFactoryCollection(
            $this->s3ClientFactory,
            $this->providedImageUrlProviderFactory,
            $this->instanceIdProviderFactory,
            $this->supplierIdProviderFactory,
        );
    }

    protected function replicate(FilledExpectedInput $input): void
    {
        $this->action->replicate(
            $this->originalImageProvider,
            ...$input->getParamValue($this->wantedReplicationSpecification),
        );
    }
}