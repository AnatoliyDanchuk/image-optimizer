<?php

namespace DomainAdapter\OriginalImagePathProvider;

class OriginalImageHttpPathProvider
{
    public function __construct(
        private readonly string $originalImageHttpPath,
    )
    {
    }

    public function getOriginalImageHttpPath(): string
    {
        return $this->originalImageHttpPath;
    }
}