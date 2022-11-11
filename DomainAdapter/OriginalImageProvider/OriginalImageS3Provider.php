<?php

namespace DomainAdapter\OriginalImageProvider;

use Domain\Image\Image;
use Domain\OriginalImageProvider;
use DomainAdapter\OriginalImagePathProvider\OriginalImageS3PathProvider;
use Framework\IntegratedService\S3\Query\GetFileContentQuery;

final class OriginalImageS3Provider implements OriginalImageProvider
{
    public function __construct(
        private readonly OriginalImageS3PathProvider $originalImageS3PathProvider,
        private readonly GetFileContentQuery $getFileContentQuery,
    )
    {
    }

    public function getOriginalImage(): Image
    {
        return new Image(
            $this->getFileContentQuery->getFileContent($this->originalImageS3PathProvider->getOriginalImageS3Path()),
        );
    }
}