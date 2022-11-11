<?php

namespace DomainAdapter\WantedImageStorage;

use Domain\Image\Image;
use Domain\WantedImageStorage\CompressedImageStorage;
use Domain\WantedImageStorage\ResizedImageStorage;
use Framework\IntegratedService\S3\Command\PutFileCommand;

final class S3ImageStorage implements CompressedImageStorage, ResizedImageStorage
{
    public function __construct(
        private readonly PutFileCommand $putFileCommand,
    )
    {
    }

    public function putImage(string $path, Image $replicatedImage): void
    {
        $this->putFileCommand->putFile($path, $replicatedImage->getContent());
    }
}