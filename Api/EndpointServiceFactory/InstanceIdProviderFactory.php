<?php

namespace Api\EndpointServiceFactory;

use Api\EndpointParamSpecification\Application\InstanceIdSpecification;
use DomainAdapter\InstanceIdProvider;
use Framework\Endpoint\EndpointInput\ExpectedInput;
use Framework\Endpoint\EndpointInput\FilledExpectedInput;
use Framework\Endpoint\EndpointTemplate\EndpointServiceFactory;

final class InstanceIdProviderFactory implements EndpointServiceFactory
{
    private InstanceIdProvider $instanceIdProvider;

    public function __construct(
        private readonly InstanceIdSpecification $instanceIdSpecification,
    )
    {
    }

    public function buildExpectedInput(): ExpectedInput
    {
        return new ExpectedInput(
            $this->instanceIdSpecification,
        );
    }

    public function applyInput(FilledExpectedInput $input): void
    {
        $this->instanceIdProvider = new InstanceIdProvider(
            $input->getParamValue($this->instanceIdSpecification),
        );
    }

    public function __invoke(): \Domain\InstanceIdProvider
    {
        return $this->instanceIdProvider;
    }
 }