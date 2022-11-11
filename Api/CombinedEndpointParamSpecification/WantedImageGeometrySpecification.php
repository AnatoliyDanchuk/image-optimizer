<?php

namespace Api\CombinedEndpointParamSpecification;

use Api\EndpointParamSpecification\WantedImageGeometry\HeightSpecification;
use Api\EndpointParamSpecification\WantedImageGeometry\WidthSpecification;
use Domain\Image\ImageGeometry;
use Framework\Endpoint\CombinedEndpointParamSpecifications\ConvertableCombinedEndpointParamSpecifications;
use Framework\Endpoint\CombinedEndpointParamSpecifications\CombinedEndpointParamSpecificationsTemplate;
use Framework\Endpoint\EndpointInput\CombinedEndpointParam;

final class WantedImageGeometrySpecification extends CombinedEndpointParamSpecificationsTemplate implements ConvertableCombinedEndpointParamSpecifications
{
    public function __construct(
        public WidthSpecification $wantedImageWidthSpecification,
        public HeightSpecification $wantedImageHeightSpecification,
    )
    {
        parent::__construct(...func_get_args());
    }

    public function toApplicationObject(CombinedEndpointParam $combinedEndpointParam): ImageGeometry
    {
        return new ImageGeometry(
            $combinedEndpointParam->getValue($this->wantedImageWidthSpecification),
            $combinedEndpointParam->getValue($this->wantedImageHeightSpecification),
        );
    }
}