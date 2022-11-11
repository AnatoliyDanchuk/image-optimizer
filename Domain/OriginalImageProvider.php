<?php

namespace Domain;

use Domain\Image\Image;

interface OriginalImageProvider
{
    public function getOriginalImage(): Image;
}