<?php

namespace Domain\Action;

use Domain\Image\ImageGeometry;
use Domain\ImageReplicator\Resizer;
use Domain\WantedImageStorage\ResizedImageStorage;
use Domain\OriginalImageProvider;

final class ReplicateImageWithResizing
{
    public function __construct(
        private readonly Resizer $resizer,
        private readonly ResizedImageStorage $resizedImageStorage,
    )
    {
    }

    public function replicate(OriginalImageProvider $originalImageProvider, string $resizedImagePath, ImageGeometry $wantedImageGeometry): void
    {
        $originalImage = $originalImageProvider->getOriginalImage();
        $resizedImage = $this->resizer->resize($originalImage, $wantedImageGeometry);
        $this->resizedImageStorage->putImage($resizedImagePath, $resizedImage);
    }
}