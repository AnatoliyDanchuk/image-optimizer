<?php

namespace Api\EndpointParamSpecification;

use Framework\Endpoint\EndpointInput\JsonBodyParamPath;
use Framework\Endpoint\EndpointInput\ParamPathCollection;
use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecificationTemplate;
use Framework\Endpoint\EndpointParamSpecification\HasRelatedErrorClass;
use Framework\Endpoint\EndpointParamSpecification\RelatedErrorClasses;
use Symfony\Component\Validator\Constraints;

final class OriginalImageHttpPathSpecification extends EndpointParamSpecificationTemplate implements HasRelatedErrorClass
{
    public function getAvailableParamPaths(): ParamPathCollection
    {
        return new ParamPathCollection(
            new JsonBodyParamPath(['original_image', 'url']),
        );
    }

    protected function getParamConstraints(): array
    {
        return [
            new Constraints\NotBlank(),
        ];
    }

    public function parseValue(string $value): string
    {
        return $value;
    }

    public function getRelatedErrorClasses(): RelatedErrorClasses
    {
        return new RelatedErrorClasses(
        );
    }
}