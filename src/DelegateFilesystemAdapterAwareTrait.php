<?php

declare(strict_types=1);

namespace Pollen\Filesystem;

use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter as DelegateFilesystemAdapter;
use League\Flysystem\FilesystemException;

/**
 * @see \League\Flysystem\FilesystemAdapter
 */
trait DelegateFilesystemAdapterAwareTrait
{
    /**
     * Delegated filesystem adapter instance.
     * @var DelegateFilesystemAdapter|null
     */
    protected ?DelegateFilesystemAdapter $delegateAdapter = null;

    /**
     * @param string $location
     *
     * @return bool
     *
     * @throws FilesystemException
     */
    public function fileExists(string $location): bool
    {
        return $this->delegateAdapter->fileExists($location);
    }

    /**
     * @param string $path
     * @param string $contents
     * @param Config $config
     *
     * @throws FilesystemException
     */
    public function write(string $path, string $contents, Config $config): void
    {
        $this->delegateAdapter->write($path, $contents, $config);
    }

    /**
     * @param string $path
     * @param mixed $contents
     * @param Config $config
     *
     * @throws FilesystemException
     */
    public function writeStream(string $path, $contents, Config $config): void
    {
        $this->delegateAdapter->writeStream($path, $contents, $config);
    }

    /**
     * @param string $path
     *
     * @return string
     *
     * @throws FilesystemException
     */
    public function read(string $path): string
    {
        return $this->delegateAdapter->read($path);
    }

    /**
     * @param string $path
     *
     * @return resource
     *
     * @throws FilesystemException
     */
    public function readStream(string $path)
    {
        return $this->delegateAdapter->readStream($path);
    }

    /**
     * @param string $path
     *
     * @throws FilesystemException
     */
    public function delete(string $path): void
    {
        $this->delegateAdapter->delete($path);
    }

    /**
     * @param string $prefix
     *
     * @throws FilesystemException
     */
    public function deleteDirectory(string $prefix): void
    {
        $this->delegateAdapter->deleteDirectory($prefix);
    }

    /**
     * @param string $path
     * @param Config $config
     *
     * @throws FilesystemException
     */
    public function createDirectory(string $path, Config $config): void
    {
        $this->delegateAdapter->createDirectory($path, $config);
    }

    /**
     * @param string $path
     * @param string $visibility
     *
     * @throws FilesystemException
     */
    public function setVisibility(string $path, string $visibility): void
    {
        $this->delegateAdapter->setVisibility($path, $visibility);
    }

    /**
     * @param string $path
     *
     * @return FileAttributes
     *
     * @throws FilesystemException
     */
    public function visibility(string $path): FileAttributes
    {
        return $this->delegateAdapter->visibility($path);
    }

    /**
     * @param string $path
     *
     * @return FileAttributes
     *
     * @throws FilesystemException
     */
    public function mimeType(string $path): FileAttributes
    {
        return $this->delegateAdapter->mimeType($path);
    }

    /**
     * @param string $path
     *
     * @return FileAttributes
     *
     * @throws FilesystemException
     */
    public function lastModified(string $path): FileAttributes
    {
        return $this->delegateAdapter->lastModified($path);
    }

    /**
     * @param string $path
     *
     * @return FileAttributes
     *
     * @throws FilesystemException
     */
    public function fileSize(string $path): FileAttributes
    {
        return $this->delegateAdapter->fileSize($path);
    }

    /**
     * @param string $path
     * @param bool $deep
     *
     * @return iterable
     *
     * @throws FilesystemException
     */
    public function listContents(string $path, bool $deep): iterable
    {
        return $this->delegateAdapter->listContents($path, $deep);
    }

    /**
     * @param string $source
     * @param string $destination
     * @param Config $config
     *
     * @throws FilesystemException
     */
    public function move(string $source, string $destination, Config $config): void
    {
        $this->delegateAdapter->move($source, $destination, $config);
    }

    /**
     * @param string $source
     * @param string $destination
     * @param Config $config
     *
     * @throws FilesystemException
     */
    public function copy(string $source, string $destination, Config $config): void
    {
        $this->delegateAdapter->copy($source, $destination, $config);
    }
}