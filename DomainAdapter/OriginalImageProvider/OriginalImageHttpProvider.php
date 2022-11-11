<?php

namespace DomainAdapter\OriginalImageProvider;

use Domain\Image\Image;
use Domain\OriginalImageProvider;
use DomainAdapter\OriginalImagePathProvider\OriginalImageHttpPathProvider;

final class OriginalImageHttpProvider implements OriginalImageProvider
{
    public function __construct(
        private readonly OriginalImageHttpPathProvider $originalImageHttpPathProvider,
    )
    {
    }

    public function getOriginalImage(): Image
    {
        return new Image(
            file_get_contents($this->originalImageHttpPathProvider->getOriginalImageHttpPath()),
        );
    }
}