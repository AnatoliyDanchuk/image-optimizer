<?php

namespace DomainAdapter\ImageReplicator;

use Domain\Image\Image;
use Domain\ImageReplicator\Compressor;

final class ImageMagickCompressor implements Compressor
{
    public function compress(Image $image): Image
    {
        $imagick = new \Imagick();
        try {
            $imagick->readImageBlob($image->getContent());

            $profiles = $imagick->getImageProfiles("icc", true);
            $imagick->stripImage();
            if(!empty($profiles)) {
                $imagick->profileImage('icc', $profiles['icc']);
            }

            $imagick->setImageCompressionQuality(85);

//            if ($image_types[2] === IMAGETYPE_JPEG) {
//                $imagick->setImageFormat('jpeg');
//                $imagick->setSamplingFactors(['2x2', '1x1', '1x1']);
//                $imagick->setInterlaceScheme(\Imagick::INTERLACE_JPEG);
//                $imagick->setColorspace(\Imagick::COLORSPACE_SRGB);
//            }

            return new Image($imagick->getImageBlob());
        } finally {
            $imagick->destroy();
        }
    }
}