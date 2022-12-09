<?php

namespace Api\EndpointParamSpecification\WantedImageGeometry;

use Framework\Endpoint\EndpointInput\JsonBodyParamPath;
use Framework\Endpoint\EndpointInput\ParamPathCollection;
use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecificationTemplate;
use Symfony\Component\Validator\Constraints;

final class WidthSpecification extends EndpointParamSpecificationTemplate
{
    public function getAvailableParamPaths(): ParamPathCollection
    {
        return new ParamPathCollection(
            new JsonBodyParamPath(['wanted_image', 'width']),
        );
    }

    protected function getParamConstraints(): array
    {
        return [
            new Constraints\NotBlank(),
        ];
    }

    public function parseValue(string $value): int
    {
        return (int) $value;
    }
}