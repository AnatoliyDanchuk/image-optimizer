<?php

namespace DomainAdapter\OriginalImageProvider;

use Domain\Image\Image;
use Domain\OriginalImageProvider;
use DomainAdapter\OriginalImagePathProvider\ProvidedImageUrlProvider;

final class OriginalImageHttpProvider implements OriginalImageProvider
{
    public function __construct(
        private readonly ProvidedImageUrlProvider $providedImageUrlProvider,
    )
    {
    }

    public function getOriginalImage(): Image
    {
        return new Image(
            file_get_contents($this->providedImageUrlProvider->getUrl()),
        );
    }
}