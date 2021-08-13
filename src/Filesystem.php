<?php

declare(strict_types=1);

namespace Pollen\Filesystem;

use League\Flysystem\DirectoryListing;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem as BaseFilesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\StorageAttributes;
use RuntimeException;

class Filesystem extends BaseFilesystem implements FilesystemInterface
{
    /**
     * Related storage manager instance.
     * @var StorageManagerInterface|null
     */
    protected ?StorageManagerInterface $storageManager = null;

    /**
     * @inheritDoc
     */
    public function asDefault(): FilesystemInterface
    {
        if (!$this->storageManager instanceof StorageManagerInterface) {
            throw new RuntimeException('Unable to retrieves related storage manager instance.');
        }
        $this->storageManager->setDefaultDisk($this);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function listContentsWithMimeType(string $location, bool $deep = self::LIST_SHALLOW): DirectoryListing
    {
        try {
            $listing = $this->listContents($location, $deep);

            $listing
                ->map(
                    function (StorageAttributes $attributes) {
                        if ($attributes instanceof FileAttributes) {
                            try {
                                $mimeType = $this->mimeType($attributes->path());

                                return new FileAttributes(
                                    $attributes->path(),
                                    $attributes->fileSize(),
                                    $attributes->visibility(),
                                    $attributes->lastModified(),
                                    $mimeType
                                );
                            } catch (FilesystemException $exception) {
                                return $attributes;
                            }
                        }
                        return $attributes;
                    }
                );

            return $listing;
        } catch (FilesystemException $e) {
            throw new RuntimeException('LocalFilesystem unable to list contents directory with mime.');
        }
    }

    /**
     * @inheritDoc
     */
    public function setStorageManager(StorageManagerInterface $storageManager): FilesystemInterface
    {
        $this->storageManager = $storageManager;

        return $this;
    }
}
