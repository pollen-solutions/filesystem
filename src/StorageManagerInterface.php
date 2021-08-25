<?php

declare(strict_types=1);

namespace Pollen\Filesystem;

use League\Flysystem\FilesystemOperator;
use Pollen\Filesystem\Drivers\LocalFilesystem;
use Pollen\Filesystem\Drivers\LocalFilesystemAdapter;
use Pollen\Filesystem\Drivers\LocalImageFilesystem;
use Pollen\Support\Concerns\BootableTraitInterface;
use Pollen\Support\Concerns\ConfigBagAwareTraitInterface;
use Pollen\Support\Proxy\ContainerProxyInterface;

/**
 * @mixin \Pollen\Filesystem\AbstractFilesystem
 */
interface StorageManagerInterface extends BootableTraitInterface, ContainerProxyInterface, ConfigBagAwareTraitInterface
{
    /**
     * Booting.
     *
     * @return static
     */
    public function boot(): StorageManagerInterface;

    /**
     * Add a filesystem instance provided by the storage manager.
     *
     * @param string $name
     * @param FilesystemInterface $disk
     *
     * @return StorageManagerInterface
     */
    public function addDisk(string $name, FilesystemInterface $disk): StorageManagerInterface;

    /**
     * Create a local filesystem adapter instance from its root directory.
     *
     * @param string $root
     * @param array $config
     *
     * @return LocalFilesystemAdapter
     */
    public function createLocalAdapter(string $root, array $config = []): LocalFilesystemAdapter;

    /**
     * Create a local filesystem instance from its root directory.
     *
     * @param string $root
     * @param array $config
     *
     * @return LocalFilesystem
     */
    public function createLocalFilesystem(string $root, array $config = []): LocalFilesystem;

    /**
     * Gets a filesystem instance provided by the storage manager from its name identifier.
     *
     * @param string|null $name
     *
     * @return FilesystemInterface|null
     */
    public function disk(?string $name = null): ?FilesystemInterface;

    /**
     * Gets the default filesystem instance provided by the storage manager.
     *
     * @return FilesystemInterface
     */
    public function getDefaultDisk(): FilesystemInterface;

    /**
     * Gets a local filesystem instance provided by the storage manager from its name identifier.
     *
     * @param string|null $name
     *
     * @return LocalFilesystem|null
     */
    public function localDisk(?string $name = null): ?LocalFilesystem;

    /**
     * Gets an image filesystem instance provided by the storage manager from its name identifier.
     *
     * @param string|null $name
     *
     * @return LocalImageFilesystem|null
     */
    public function localImageDisk(?string $name = null): ?LocalImageFilesystem;

    /**
     * Register a filesystem instance provided by the storage manager by registered driver name and adapter arguments.
     *
     * @param string $diskName
     * @param string $driverName
     * @param mixed ...$adapterArgs
     *
     * @return LocalFilesystem
     */
    public function registerDisk(string $diskName, string $driverName, ...$adapterArgs): FilesystemOperator;

    /**
     * Register a filesystem driver.
     *
     * @param string $name
     * @param FilesystemDriverInterface $driver
     *
     * @return static
     */
    public function registerDriver(string $name, FilesystemDriverInterface $driver): StorageManagerInterface;

    /**
     * Register a local filesystem instance provided by the storage manager.
     *
     * @param string $name
     * @param string $root
     * @param array|null $config
     *
     * @return LocalFilesystem
     */
    public function registerLocalDisk(string $name, string $root, ?array $config = null): LocalFilesystem;

    /**
     * Register an image filesystem instance provided by the storage manager.
     *
     * @param string $name
     * @param string $root
     * @param array|null $config
     *
     * @return LocalImageFilesystem
     */
    public function registerLocalImageDisk(
        string $name,
        string $root,
        ?array $config = null
    ): LocalImageFilesystem;

    /**
     * Register an S3 filesystem instance provided by the storage manager.
     *
     * @param string $name
     * @param array $client
     * @param string $bucket
     * @param array|null $config
     *
     * @return FilesystemInterface
     */
    public function registerS3Disk(
        string $name,
        array $client,
        string $bucket,
        ?array $config = null
    ): FilesystemInterface;

    /**
     * Set the default filesystem provided by the storage manager.
     *
     * @param FilesystemInterface $defaultDisk
     *
     * @return StorageManagerInterface
     */
    public function setDefaultDisk(FilesystemInterface $defaultDisk): StorageManagerInterface;
}