<?php

namespace Api\EndpointServiceFactory;

use Api\EndpointParamSpecification\Application\SupplierIdSpecification;
use DomainAdapter\SupplierIdProvider;
use Framework\Endpoint\EndpointInput\ExpectedInput;
use Framework\Endpoint\EndpointInput\FilledExpectedInput;
use Framework\Endpoint\EndpointTemplate\EndpointServiceFactory;

final class SupplierIdProviderFactory implements EndpointServiceFactory
{
    private SupplierIdProvider $supplierIdProvider;

    public function __construct(
        private readonly SupplierIdSpecification $supplierIdSpecification,
    )
    {
    }

    public function buildExpectedInput(): ExpectedInput
    {
        return new ExpectedInput(
            $this->supplierIdSpecification,
        );
    }

    public function applyInput(FilledExpectedInput $input): void
    {
        $this->supplierIdProvider = new SupplierIdProvider(
            $input->getParamValue($this->supplierIdSpecification),
        );
    }

    public function __invoke(): \Domain\SupplierIdProvider
    {
        return $this->supplierIdProvider;
    }
 }