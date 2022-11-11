<?php

namespace Api\EndpointParamSpecification\WantedImageGeometry;

use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecificationTemplate;
use Framework\Endpoint\EndpointParamSpecification\InJsonHttpBodyAllowed;
use Symfony\Component\Validator\Constraints;

final class WidthSpecification extends EndpointParamSpecificationTemplate implements
    InJsonHttpBodyAllowed
{
    public function getJsonItemPath(): array
    {
        return ['wanted_image', 'width'];
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