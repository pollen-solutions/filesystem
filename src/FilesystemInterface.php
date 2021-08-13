<?php

declare(strict_types=1);

namespace Pollen\Filesystem;

use League\Flysystem\DirectoryListing;
use League\Flysystem\FilesystemOperator;

interface FilesystemInterface extends FilesystemOperator
{
    /**
     * Enables the filesystem as default in the related storage manager.
     *
     * @return static
     */
    public function asDefault(): FilesystemInterface;

    /**
     * Lists all file of a directory with their mime type information.
     *
     * @param string $location
     * @param bool $deep
     *
     * @return DirectoryListing
     */
    public function listContentsWithMimeType(string $location, bool $deep = self::LIST_SHALLOW): DirectoryListing;

    /**
     * Set the related storage manager instance.
     *
     * @param StorageManagerInterface $storageManager
     *
     * @return static
     */
    public function setStorageManager(StorageManagerInterface $storageManager): FilesystemInterface;
}