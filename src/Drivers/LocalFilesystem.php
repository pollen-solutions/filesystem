<?php

declare(strict_types=1);

namespace Pollen\Filesystem\Drivers;

use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemException;
use League\Flysystem\PathNormalizer;
use League\Flysystem\StorageAttributes;
use Pollen\Filesystem\AbstractFilesystem;
use Pollen\Filesystem\FilesystemAdapterInterface;
use Pollen\Filesystem\FilesystemHttpAwareTrait;
use SplFileInfo;

class LocalFilesystem extends AbstractFilesystem
{
    use FilesystemHttpAwareTrait;

    /**
     * Related Local Filesystem adapter instance.
     * @var LocalFilesystemAdapter
     */
    protected FilesystemAdapterInterface $adapter;

    /**
     * @param LocalFilesystemAdapter $adapter
     * @param array $config
     * @param PathNormalizer|null $pathNormalizer
     */
    public function __construct(
        LocalFilesystemAdapter $adapter,
        array $config = [],
        PathNormalizer $pathNormalizer = null
    ) {
        parent::__construct($adapter, $config, $pathNormalizer);
    }

    /**
     * Gets a file contents from its path.
     *
     * @param string $path
     *
     * @return string|null
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
     * Gets the absolute path of a resource from its path.
     *
     * @param string $path
     *
     * @return string
     */
    public function getAbsolutePath(string $path = '/'): string
    {
        return $this->adapter->getAbsolutePath($path);
    }

    /**
     * Gets the SplFileInfo instance of a resource from its path.
     *
     * @param string $path
     *
     * @return SplFileInfo
     */
    public function getSplFileInfo(string $path = '/'): SplFileInfo
    {
        return $this->adapter->getSplFileInfo($path);
    }

    /**
     * Gets the storage file attributes of a resource form its path.
     *
     * @param string $path
     *
     * @return StorageAttributes|DirectoryAttributes|FileAttributes
     */
    public function getStorageAttributes(string $path = '/'): StorageAttributes
    {
        return $this->adapter->getStorageAttributes($path);
    }
}