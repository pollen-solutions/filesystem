<?php

declare(strict_types=1);

namespace Pollen\Filesystem;

use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use League\Flysystem\StorageAttributes;
use SplFileInfo;

interface LocalFilesystemInterface extends FilesystemInterface, FilesystemHttpAwareTraitInterface
{
    /**
     * Gets a file contents from its path.
     *
     * @param string $path
     *
     * @return string|null
     */
    public function __invoke(string $path): ?string;

    /**
     * Gets the absolute path of a resource from its path.
     *
     * @param string $path
     *
     * @return string
     */
    public function getAbsolutePath(string $path = '/'): string;

    /**
     * Gets the SplFileInfo instance of a resource from its path.
     *
     * @param string $path
     *
     * @return SplFileInfo
     */
    public function getSplFileInfo(string $path = '/'): SplFileInfo;

    /**
     * Gets the storage file attributes of a resource form its path.
     *
     * @param string $path
     *
     * @return StorageAttributes|DirectoryAttributes|FileAttributes
     */
    public function getStorageAttributes(string $path = '/'): StorageAttributes;
}