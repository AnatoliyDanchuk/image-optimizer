<?php

namespace DomainAdapter;

class SupplierIdProvider implements \Domain\SupplierIdProvider
{
    public function __construct(
        private readonly string $supplierId,
    )
    {
    }

    public function getSupplierId(): string
    {
        return $this->supplierId;
    }
}