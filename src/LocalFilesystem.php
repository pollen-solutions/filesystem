<?php

declare(strict_types=1);

namespace Pollen\Filesystem;

use League\Flysystem\FilesystemException;
use League\Flysystem\PathNormalizer;
use League\Flysystem\StorageAttributes;
use SplFileInfo;

class LocalFilesystem extends AbstractFilesystem implements LocalFilesystemInterface
{
    use FilesystemHttpAwareTrait;

    /**
     * Related Local Filesystem adapter instance.
     * @var LocalFilesystemAdapterInterface
     */
    protected FilesystemAdapterInterface $adapter;

    /**
     * @param LocalFilesystemAdapterInterface $adapter
     * @param array $config
     * @param PathNormalizer|null $pathNormalizer
     */
    public function __construct(
        LocalFilesystemAdapterInterface $adapter,
        array $config = [],
        PathNormalizer $pathNormalizer = null
    ) {
        parent::__construct($adapter, $config, $pathNormalizer);
    }

    /**
     * @inheritDoc
     */
    public function __invoke(string $path): ?string
    {
        try {
            if ($this->fileExists($path)) {
                return $this->read($path);
            }
            return null;
        } catch (FilesystemException $exception) {
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function getAbsolutePath(string $path = '/'): string
    {
        return $this->adapter->getAbsolutePath($path);
    }

    /**
     * @inheritDoc
     */
    public function getSplFileInfo(string $path = '/'): SplFileInfo
    {
        return $this->adapter->getSplFileInfo($path);
    }

    /**
     * @inheritDoc
     */
    public function getStorageAttributes(string $path = '/'): StorageAttributes
    {
        return $this->adapter->getStorageAttributes($path);
    }
}