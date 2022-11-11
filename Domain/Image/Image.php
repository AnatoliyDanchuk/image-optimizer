<?php

namespace  Domain\Image;

final class Image
{
    public function __construct(
        private readonly string $content,
    )
    {
    }

    public function getContent(): string
    {
        return $this->content;
    }
}