<?php

namespace Domain\Action;

use Domain\ImageReplicator\Compressor;
use Domain\WantedImageStorage\CompressedImageStorage;
use Domain\OriginalImageProvider;

final class ReplicateImageWithCompression
{
    public function __construct(
        private readonly Compressor $imageOptimizer,
        private readonly CompressedImageStorage $optimizedImageStorage,
    )
    {
    }

    public function replicate(OriginalImageProvider $originalImageProvider, string $compressedImagePath): void
    {
        $originalImage = $originalImageProvider->getOriginalImage();
        $compressedImage = $this->imageOptimizer->compress($originalImage);
        $this->optimizedImageStorage->putImage($compressedImagePath, $compressedImage);
    }
}