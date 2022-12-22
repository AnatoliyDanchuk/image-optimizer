<?php

namespace DomainAdapter\OriginalImagePathProvider;

class ProvidedImageUrlProvider
{
    public function __construct(
        private readonly string $providedImageUrl,
    )
    {
    }

    public function getUrl(): string
    {
        return $this->providedImageUrl;
    }
}