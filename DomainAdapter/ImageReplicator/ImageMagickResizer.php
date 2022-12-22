<?php

namespace DomainAdapter\ImageReplicator;

use Domain\Image\ImageGeometry;
use Domain\Image\Image;
use Domain\ImageReplicator\Resizer;

class ImageMagickResizer implements Resizer
{
    public function resize(Image $image, ImageGeometry $wantedImageGeometry): Image
    {
        $imagick = new \Imagick();
        try {
            $imagick->readImageBlob($image->getContent());

            $imagick->resizeImage(
                $wantedImageGeometry->getWidth(),
                $wantedImageGeometry->getHeight(),
                \Imagick::FILTER_LANCZOS,
                0.9,
                true,
            );

            $imagick->extentImage(
                $wantedImageGeometry->getWidth(),
                $wantedImageGeometry->getHeight(),
                - (($wantedImageGeometry->getWidth() - $imagick->getImageGeometry()['width']) / 2),
                - (($wantedImageGeometry->getHeight() - $imagick->getImageGeometry()['height']) / 2),
            );

            return new Image($imagick->getImageBlob());
        } finally {
            $imagick->destroy();
        }
    }
}