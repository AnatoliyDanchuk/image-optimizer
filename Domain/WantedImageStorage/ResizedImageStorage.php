<?php

namespace Domain\WantedImageStorage;

use Domain\Image\Image;

interface ResizedImageStorage
{
    public function putImage(string $path, Image $replicatedImage): void;
}