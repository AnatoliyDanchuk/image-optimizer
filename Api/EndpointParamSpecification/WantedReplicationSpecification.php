<?php

namespace Api\EndpointParamSpecification;

use Domain\Image\ImageGeometry;
use Framework\Endpoint\EndpointInput\JsonBodyParamPath;
use Framework\Endpoint\EndpointInput\ParamPathCollection;
use Framework\Endpoint\EndpointParamSpecification\EndpointParamSpecificationTemplate;
use Symfony\Component\Validator\Constraints;

final class WantedReplicationSpecification extends EndpointParamSpecificationTemplate
{

    public function getAvailableParamPaths(): ParamPathCollection
    {
        return new ParamPathCollection(
            new JsonBodyParamPath(['thumbs']),
        );
    }

    protected function getParamConstraints(): array
    {
        return [
            new Constraints\NotBlank(),
            new Constraints\Count(min: 1),
        ];
    }

    public function parseValue(string|array $value): array
    {
        $wantedReplication = [];
        foreach($value as $item) {
            $wantedReplication[] = new ImageGeometry($item->width, $item->height);
        }

        return $wantedReplication;
    }
}