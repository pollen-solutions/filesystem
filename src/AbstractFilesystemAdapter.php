<?php

declare(strict_types=1);

namespace Pollen\Filesystem;

use League\Flysystem\StorageAttributes;
use Pollen\Filesystem\Exception\UnableToGetStorageAttributes;

class AbstractFilesystemAdapter implements FilesystemAdapterInterface
{
    use DelegateFilesystemAdapterAwareTrait;

    /**
     * @inheritDoc
     */
    public function getStorageAttributes(string $path = '/'): StorageAttributes
    {
        throw new UnableToGetStorageAttributes($path);
    }
}