<?php

declare(strict_types=1);

namespace Pollen\Filesystem;

use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use League\Flysystem\Local\LocalFilesystemAdapter as BaseLocalFilesystemAdapter;
use League\Flysystem\PathPrefixer;
use League\Flysystem\StorageAttributes;
use League\Flysystem\SymbolicLinkEncountered;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\UnixVisibility\VisibilityConverter;
use League\MimeTypeDetection\FinfoMimeTypeDetector;
use League\MimeTypeDetection\MimeTypeDetector;
use SplFileInfo;

class LocalFilesystemAdapter extends AbstractFilesystemAdapter implements LocalFilesystemAdapterInterface
{
    /**
     * @var int
     */
    public const SKIP_LINKS = 0001;

    /**
     * @var int
     */
    public const DISALLOW_LINKS = 0002;

    /**
     * @var int
     */
    protected int $delegatedLinkHandling;

    /**
     * @var MimeTypeDetector
     */
    protected MimeTypeDetector $delegatedMimeTypeDetector;

    /**
     * @var PathPrefixer
     */
    protected PathPrefixer $delegatedPrefixer;

    /**
     * @var VisibilityConverter
     */
    protected VisibilityConverter $delegatedVisibility;

    /**
     * @var int
     */
    protected int $delegatedWriteFlags;

    /**
     * @param string $location
     * @param VisibilityConverter|null $visibility
     * @param int $writeFlags
     * @param int $linkHandling
     * @param MimeTypeDetector|null $mimeTypeDetector
     */
    public function __construct(
        string $location,
        VisibilityConverter $visibility = null,
        int $writeFlags = LOCK_EX,
        int $linkHandling = BaseLocalFilesystemAdapter::DISALLOW_LINKS,
        MimeTypeDetector $mimeTypeDetector = null
    ) {
        $this->delegatedPrefixer = new PathPrefixer($location, DIRECTORY_SEPARATOR);
        $this->delegatedWriteFlags = $writeFlags;
        $this->delegatedLinkHandling = $linkHandling;
        $this->delegatedVisibility = $visibility ?? new PortableVisibilityConverter();
        $this->delegatedMimeTypeDetector = $mimeTypeDetector ?: new FinfoMimeTypeDetector();

        $this->delegateAdapter = new BaseLocalFilesystemAdapter(
            $location,
            $visibility,
            $writeFlags,
            $linkHandling,
            $mimeTypeDetector
        );
    }

    /**
     * @inheritDoc
     */
    public function getAbsolutePath(string $path = '/'): string
    {
        return $this->delegatedPrefixer->prefixPath($path);
    }

    /**
     * @inheritDoc
     */
    public function getSplFileInfo(string $path = '/'): SplFileInfo
    {
        return new SplFileInfo($this->getAbsolutePath($path));
    }

    /**
     * @inheritDoc
     */
    public function getStorageAttributes(string $path = '/'): StorageAttributes
    {
        $fileInfo = $this->getSplFileInfo($path);

        if ($fileInfo->isLink()) {
            if (!$this->delegatedLinkHandling || !self::SKIP_LINKS) {
                throw SymbolicLinkEncountered::atLocation($fileInfo->getPathname());
            }
        }

        $path = $this->delegatedPrefixer->stripPrefix($fileInfo->getPathname());
        $lastModified = $fileInfo->getMTime();
        $isDirectory = $fileInfo->isDir();
        $permissions = $fileInfo->getPerms();
        $visibility = $isDirectory
            ? $this->delegatedVisibility->inverseForDirectory($permissions)
            : $this->delegatedVisibility->inverseForFile($permissions);

        return $isDirectory ? new DirectoryAttributes($path, $visibility, $lastModified) : new FileAttributes(
            str_replace('\\', '/', $path),
            $fileInfo->getSize(),
            $visibility,
            $lastModified
        );
    }
}