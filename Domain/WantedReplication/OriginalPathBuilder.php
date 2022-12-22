<?php

namespace Domain\WantedReplication;

use Domain\InstanceIdProvider;
use Domain\SupplierIdProvider;

final class OriginalPathBuilder
{
    public function __construct(
        private readonly InstanceIdProvider $instanceIdProvider,
        private readonly SupplierIdProvider $supplierIdProvider,
        private readonly FileNameBuilder $fileNameBuilder,
    )
    {
    }

    public function buildReplicationPath(): string
    {
        return implode('/', [
            $this->instanceIdProvider->getInstanceId(),
            'original',
            $this->supplierIdProvider->getSupplierId(),
            $this->fileNameBuilder->getFileName(),
        ]);
    }
}