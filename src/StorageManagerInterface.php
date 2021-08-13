<?php

declare(strict_types=1);

namespace Pollen\Filesystem;

use Pollen\Support\Concerns\ConfigBagAwareTraitInterface;
use Pollen\Support\Proxy\ContainerProxyInterface;

/**
 * @mixin \Pollen\Filesystem\AbstractFilesystem
 */
interface StorageManagerInterface extends ContainerProxyInterface, ConfigBagAwareTraitInterface
{
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
     * Add a local filesystem instance provided by the storage manager.
     *
     * @param string $name
     * @param LocalFilesystemInterface $disk
     *
     * @return StorageManagerInterface
     */
    public function addLocalDisk(string $name, LocalFilesystemInterface $disk): StorageManagerInterface;

    /**
     * Create a local filesystem adapter instance from its root directory.
     *
     * @param string $root
     * @param array $config
     *
     * @return LocalFilesystemAdapterInterface
     */
    public function createLocalAdapter(string $root, array $config = []): LocalFilesystemAdapterInterface;

    /**
     * Create a local filesystem instance from its root directory.
     *
     * @param string $root
     * @param array $config
     *
     * @return LocalFilesystemInterface
     */
    public function createLocalFilesystem(string $root, array $config = []): LocalFilesystemInterface;

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
     * @return LocalFilesystemInterface|null
     */
    public function localDisk(?string $name = null): ?LocalFilesystemInterface;

    /**
     * Gets an image filesystem instance provided by the storage manager from its name identifier.
     *
     * @param string|null $name
     *
     * @return LocalImageFilesystemInterface|null
     */
    public function localImageDisk(?string $name = null): ?LocalImageFilesystemInterface;

    /**
     * Register a local filesystem instance provided by the storage manager from its root directory.
     *
     * @param string $name
     * @param string $root
     * @param array $config
     *
     * @return LocalFilesystemInterface
     */
    public function registerLocalDisk(string $name, string $root, array $config = []): LocalFilesystemInterface;

    /**
     * Register an image filesystem instance provided by the storage manager from its root directory.
     *
     * @param string $name
     * @param string $root
     * @param array $config
     *
     * @return LocalImageFilesystemInterface
     */
    public function registerLocalImageDisk(string $name, string $root, array $config = []): LocalImageFilesystemInterface;

    /**
     * Set the default filesystem provided by the storage manager.
     *
     * @param FilesystemInterface $defaultDisk
     *
     * @return StorageManagerInterface
     */
    public function setDefaultDisk(FilesystemInterface $defaultDisk): StorageManagerInterface;
}