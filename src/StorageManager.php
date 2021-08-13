<?php

declare(strict_types=1);

namespace Pollen\Filesystem;

use League\Flysystem\Local\LocalFilesystemAdapter as BaseLocalFilesystemAdapter;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\UnixVisibility\VisibilityConverter;
use Pollen\Support\Concerns\ConfigBagAwareTrait;
use Pollen\Support\Exception\ManagerRuntimeException;
use Pollen\Support\Proxy\ContainerProxy;
use Psr\Container\ContainerInterface as Container;
use RuntimeException;

/**
 * @mixin \Pollen\Filesystem\AbstractFilesystem
 */
class StorageManager implements StorageManagerInterface
{
    use ConfigBagAwareTrait;
    use ContainerProxy;

    /**
     * Storage manager main instance.
     * @var StorageManagerInterface|null
     */
    private static ?StorageManagerInterface $instance = null;

    /**
     * List of registered filesystem instances.
     * @var array<string, FilesystemInterface>|array
     */
    private array $disks = [];

    /**
     * Default filesystem instance.
     * @var FilesystemInterface|null
     */
    private ?FilesystemInterface $defaultDisk = null;

    /**
     * @param array $config
     * @param Container|null $container
     *
     * @return void
     */
    public function __construct(array $config = [], ?Container $container = null)
    {
        $this->setConfig($config);

        if ($container !== null) {
            $this->setContainer($container);
        }

        if (!self::$instance instanceof static) {
            self::$instance = $this;
        }
    }

    /**
     * Delegate methods call of default filesystem instance.
     *
     * @param $method
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return $this->getDefaultDisk()->$method(...$arguments);
    }

    /**
     * Retrieves storage manager main instance.
     *
     * @return static
     */
    public static function getInstance(): StorageManagerInterface
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }
        throw new ManagerRuntimeException(sprintf('Unavailable [%s] instance', __CLASS__));
    }

    /**
     * @inheritDoc
     */
    public function addDisk(string $name, FilesystemInterface $disk): StorageManagerInterface
    {
        $this->disks[$name] = $disk->setStorageManager($this);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addLocalDisk(string $name, LocalFilesystemInterface $disk): StorageManagerInterface
    {
        return $this->addDisk($name, $disk);
    }

    /**
     * @inheritDoc
     */
    public function createLocalAdapter(string $root, array $config = []): LocalFilesystemAdapterInterface
    {
        $visibility = $config['visibility'] ?? null;
        if (!$visibility instanceof VisibilityConverter) {
            $visibility = is_array($visibility)? PortableVisibilityConverter::fromArray($visibility) : null;
        }

        $writeFlags = (int)($config['write_flags'] ?? LOCK_EX);

        $linkHandling = ($config['links'] ?? null) === 'skip'
            ? BaseLocalFilesystemAdapter::SKIP_LINKS : BaseLocalFilesystemAdapter::DISALLOW_LINKS;

        return new LocalFilesystemAdapter($root, $visibility, $writeFlags, $linkHandling);
    }

    /**
     * @inheritDoc
     */
    public function createLocalFilesystem(string $root, array $config = []): LocalFilesystemInterface
    {
        $adapter = $this->createLocalAdapter($root, $config);

        return new LocalFilesystem($adapter);
    }

    /**
     * @inheritDoc
     */
    public function disk(?string $name = null): ?FilesystemInterface
    {
        if ($name === null) {
            return $this->getDefaultDisk();
        }

        return $this->disks[$name] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultDisk(): FilesystemInterface
    {
        if ($this->defaultDisk === null) {
            $this->defaultDisk = $this->createLocalFilesystem(getcwd());
        }

        return $this->defaultDisk;
    }

    /**
     * @inheritDoc
     */
    public function localDisk(?string $name = null): ?LocalFilesystemInterface
    {
        if (($disk = $this->disk($name)) && $disk instanceof LocalFilesystemInterface) {
            return $disk;
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function localImageDisk(?string $name = null): ?LocalImageFilesystemInterface
    {
        if (($disk = $this->disk($name)) && $disk instanceof LocalImageFilesystemInterface) {
            return $disk;
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function registerLocalDisk(string $name, string $root, array $config = []): LocalFilesystemInterface
    {
        $disk = $this->createLocalFilesystem($root, $config);

        $this->addLocalDisk($name, $disk);

        $exists = $this->disk($name);
        if ($exists instanceof LocalFilesystemInterface) {
            return $exists;
        }
        throw new RuntimeException(sprintf('StorageManager unable to register local disk [%s]', $name));
    }

    /**
     * @inheritDoc
     */
    public function registerLocalImageDisk(string $name, string $root, array $config = []): LocalImageFilesystemInterface
    {
        $adapter = $this->createLocalAdapter($root, $config);
        $disk = new LocalImageFilesystem($adapter);

        $this->addLocalDisk($name, $disk);

        $exists = $this->disk($name);
        if ($exists instanceof LocalImageFilesystemInterface) {
            return $exists;
        }
        throw new RuntimeException(sprintf('StorageManager unable to register local image disk [%s]', $name));
    }

    /**
     * @inheritDoc
     */
    public function setDefaultDisk(FilesystemInterface $defaultDisk): StorageManagerInterface
    {
        $this->defaultDisk = $defaultDisk;

        return $this;
    }
}