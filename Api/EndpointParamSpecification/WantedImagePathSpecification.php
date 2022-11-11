<?php

namespace Api\EndpointParamSpecification;

use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecificationTemplate;
use Framework\Endpoint\EndpointParamSpecification\HasRelatedErrorClass;
use Framework\Endpoint\EndpointParamSpecification\InJsonHttpBodyAllowed;
use Framework\Endpoint\EndpointParamSpecification\RelatedErrorClasses;
use Framework\IntegratedService\S3\Exception\S3ConnectionError;
use Symfony\Component\Validator\Constraints;

final class WantedImagePathSpecification extends EndpointParamSpecificationTemplate implements
    InJsonHttpBodyAllowed,
    HasRelatedErrorClass
{
    public function getJsonItemPath(): array
    {
        return ['wanted_image', 's3_path'];
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
            S3ConnectionError::class,
        );
    }
}