<?php

namespace Framework\Endpoint\CombinedEndpointParamSpecifications;

use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecificationTemplate;

abstract class CombinedEndpointParamSpecificationsTemplate implements CombinedEndpointParamSpecifications
{
    private array $items;

    /**
     * @param self|EndpointParamSpecificationTemplate ...$items
     */
    protected function __construct(
        ...$items
    )
    {
        $this->items = $items;
    }

    /** @return self|EndpointParamSpecificationTemplate[] */
    final public function getOnlyChildren(): array
    {
        return $this->items;
    }

    /** @return EndpointParamSpecificationTemplate[] */
    final public function getAllParams(): array
    {
        $groupedParams = [];
        $separatedParams = [];
        foreach ($this->items as $item) {
            if ($item instanceof self) {
                $groupedParams[] = $item->getAllParams();
            } else {
                $separatedParams[] = $item;
            }
        }

        return array_merge($separatedParams, ...$groupedParams);
    }
}