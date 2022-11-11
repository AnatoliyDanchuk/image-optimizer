<?php

namespace Domain\Image;

final class ImageGeometry
{
    public function __construct(
        private int $width,
        private int $height,
    )
    {
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }
}