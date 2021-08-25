<?php

declare(strict_types=1);

namespace Pollen\Filesystem;

use League\Flysystem\FilesystemOperator;
use League\Flysystem\PathNormalizer;
use League\Flysystem\FilesystemAdapter;
use Pollen\Support\Proxy\ContainerProxyInterface;
use Pollen\Support\Proxy\StorageProxyInterface;

interface FilesystemDriverInterface extends ContainerProxyInterface, StorageProxyInterface
{
    /**
     * @param mixed ...$args

     * @return FilesystemOperator
     */
    public function __invoke(...$args): FilesystemOperator;

    /**
     * Retrieves Filesystem instance.
     *
     * @param FilesystemAdapter $adapter
     *
     * @return FilesystemOperator
     */
    public function getFilesystem(FilesystemAdapter $adapter): FilesystemOperator;

    /**
     * Retrieves Filesystem adapter instance.
     *
     * @param mixed ...$args
     *
     * @return FilesystemAdapter|null
     */
    public function getAdapter(...$args): ?FilesystemAdapter;

    /**
     * Parses list of driver arguments.
     *
     * @param mixed ...$args
     *
     * @return array
     */
    public function parseArgs(...$args): array;

    /**
     * Sets the filesystem configuration parameters.
     *
     * @param array $config
     *
     * @return static
     */
    public function setConfig(array $config): FilesystemDriverInterface;

    /**
     * Sets the filesystem path normalizer instance.
     *
     * @param PathNormalizer $pathNormalizer
     *
     * @return static
     */
    public function setPathNormalizer(PathNormalizer $pathNormalizer): FilesystemDriverInterface;
}