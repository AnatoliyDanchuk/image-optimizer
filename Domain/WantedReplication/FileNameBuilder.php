<?php

namespace Domain\WantedReplication;

use DomainAdapter\OriginalImagePathProvider\ProvidedImageUrlProvider;

final class FileNameBuilder
{
    public function __construct(
        private readonly ProvidedImageUrlProvider $providedImageUrlProvider,
    )
    {
    }

    public function getFileName(): string
    {
        return basename(parse_url($this->providedImageUrlProvider->getUrl(), \PHP_URL_PATH));
    }
}