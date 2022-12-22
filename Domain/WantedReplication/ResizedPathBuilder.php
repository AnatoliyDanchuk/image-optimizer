<?php

namespace Domain\WantedReplication;

use Domain\Image\ImageGeometry;
use Domain\InstanceIdProvider;
use Domain\SupplierIdProvider;

final class ResizedPathBuilder
{
    public function __construct(
        private readonly InstanceIdProvider $instanceIdProvider,
        private readonly SupplierIdProvider $supplierIdProvider,
        private readonly FileNameBuilder $fileNameBuilder,
    )
    {
    }

    public function buildReplicationPath(ImageGeometry $wantedImageGeometry): string
    {
        return implode('/', [
            $this->instanceIdProvider->getInstanceId(),
            'resized',
            $wantedImageGeometry->getWidth() . 'x',
            $this->supplierIdProvider->getSupplierId(),
            $this->fileNameBuilder->getFileName(),
        ]);
    }
}