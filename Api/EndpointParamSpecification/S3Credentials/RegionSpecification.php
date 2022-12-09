<?php

namespace Api\EndpointParamSpecification\S3Credentials;

use Framework\Endpoint\EndpointInput\JsonBodyParamPath;
use Framework\Endpoint\EndpointInput\ParamPathCollection;
use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecificationTemplate;
use Framework\Endpoint\EndpointParamSpecification\HasRelatedErrorClass;
use Framework\Endpoint\EndpointParamSpecification\RelatedErrorClasses;
use Framework\IntegratedService\S3\Exception\S3ConnectionError;
use Framework\IntegratedService\S3\Exception\S3FileNotFound;

final class RegionSpecification extends EndpointParamSpecificationTemplate implements HasRelatedErrorClass
{
    public function getAvailableParamPaths(): ParamPathCollection
    {
        return new ParamPathCollection(
            new JsonBodyParamPath(['s3', 'aws_region']),
        );
    }

    protected function getParamConstraints(): array
    {
        return [
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
            S3FileNotFound::class,
        );
    }
}