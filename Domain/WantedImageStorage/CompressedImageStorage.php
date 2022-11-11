<?php

namespace Domain\WantedImageStorage;

use Domain\Image\Image;

interface CompressedImageStorage
{
    public function putImage(string $path, Image $replicatedImage): void;
}