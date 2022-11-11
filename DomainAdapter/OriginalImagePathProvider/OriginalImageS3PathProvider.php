<?php

namespace DomainAdapter\OriginalImagePathProvider;

class OriginalImageS3PathProvider
{
    public function __construct(
        private readonly string $originalImageS3Path,
    )
    {
    }

    public function getOriginalImageS3Path(): string
    {
        return $this->originalImageS3Path;
    }
}