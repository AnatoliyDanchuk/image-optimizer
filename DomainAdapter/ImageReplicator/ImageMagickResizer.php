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

            $currentImageSize = $this->getImageGeometry($imagick);
            $imageGeometryForScaling = $this->calculateImageGeometryForScaling($currentImageSize, $wantedImageGeometry);

            $imagick->resizeImage(
                $imageGeometryForScaling->getWidth(),
                $imageGeometryForScaling->getHeight(),
                \Imagick::FILTER_LANCZOS,
                0.9,
                true,
            );

            $imagick->cropImage($wantedImageGeometry->getWidth(),$wantedImageGeometry->getHeight(),0,0);

            return new Image($imagick->getImageBlob());
        } finally {
            $imagick->destroy();
        }
    }

    private function getImageGeometry(\Imagick $imagick): ImageGeometry
    {
        $imageGeometry = $imagick->getImageGeometry();
        return new ImageGeometry($imageGeometry['width'], $imageGeometry['height']);
    }

    private function calculateImageGeometryForScaling(ImageGeometry $currentImageSize, ImageGeometry $wantedImageGeometry): ImageGeometry
    {
        if ($currentImageSize->getWidth() > $currentImageSize->getHeight()) {
            $heightForScaling = $wantedImageGeometry->getHeight();
            $widthForScaling = ($heightForScaling / $currentImageSize->getHeight()) * $currentImageSize->getWidth();
        } else {
            $widthForScaling = $wantedImageGeometry->getWidth();
            $heightForScaling = ($widthForScaling / $currentImageSize->getWidth()) * $currentImageSize->getHeight();
        }
        return new ImageGeometry($widthForScaling, $heightForScaling);
    }
}