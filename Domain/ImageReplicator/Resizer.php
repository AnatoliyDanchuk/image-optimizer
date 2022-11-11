<?php

namespace Domain\ImageReplicator;

use Domain\Image\ImageGeometry;
use Domain\Image\Image;

interface Resizer
{
    public function resize(Image $image, ImageGeometry $wantedImageGeometry): Image;
}