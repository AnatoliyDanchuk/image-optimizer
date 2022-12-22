<?php

namespace Domain\Action;

use Domain\Image\ImageGeometry;
use Domain\ImageReplicator\Compressor;
use Domain\ImageReplicator\Resizer;
use Domain\WantedImageStorage\CompressedImageStorage;
use Domain\WantedImageStorage\ResizedImageStorage;
use Domain\OriginalImageProvider;
use Domain\WantedReplication\OriginalPathBuilder;
use Domain\WantedReplication\ResizedPathBuilder;

final class ReplicateImageWithResizing
{
    public function __construct(
        private readonly Compressor $imageOptimizer,
        private readonly OriginalPathBuilder $wantedOriginalPathBuilder,
        private readonly CompressedImageStorage $optimizedImageStorage,

        private readonly Resizer $resizer,
        private readonly ResizedPathBuilder $wantedResizedPathBuilder,
        private readonly ResizedImageStorage $resizedImageStorage,
    )
    {
    }

    public function replicate(OriginalImageProvider $originalImageProvider, ImageGeometry ...$wantedReplication): void
    {
        $originalImage = $originalImageProvider->getOriginalImage();

        $compressedImage = $this->imageOptimizer->compress($originalImage);

        $compressedImagePath = $this->wantedOriginalPathBuilder->buildReplicationPath();
        $this->optimizedImageStorage->putImage($compressedImagePath, $compressedImage);

        foreach ($wantedReplication as $wantedImageGeometry) {
            $resizedImage = $this->resizer->resize($compressedImage, $wantedImageGeometry);
            $resizedImagePath = $this->wantedResizedPathBuilder->buildReplicationPath($wantedImageGeometry);
            $this->resizedImageStorage->putImage($resizedImagePath, $resizedImage);
        }
    }
}