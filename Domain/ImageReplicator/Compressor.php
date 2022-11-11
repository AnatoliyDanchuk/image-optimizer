<?php

namespace Domain\ImageReplicator;

use Domain\Image\Image;

interface Compressor
{
    public function compress(Image $image): Image;
}