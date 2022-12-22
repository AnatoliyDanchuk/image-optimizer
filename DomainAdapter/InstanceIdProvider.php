<?php

namespace DomainAdapter;

class InstanceIdProvider implements \Domain\InstanceIdProvider
{
    public function __construct(
        private readonly string $instanceId,
    )
    {
    }

    public function getInstanceId(): string
    {
        return $this->instanceId;
    }
}