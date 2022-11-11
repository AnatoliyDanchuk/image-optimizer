<?php

namespace Framework\Endpoint\EndpointTemplate;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

final class EndpointServiceFactoryCollection implements IteratorAggregate
{
    private array $factories;

    public function __construct(EndpointServiceFactory ...$endpointServiceFactory)
    {
        $this->factories = $endpointServiceFactory;
    }

    /**
     * @return Traversable|EndpointServiceFactory[]
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->factories);
    }
}