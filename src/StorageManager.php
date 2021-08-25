<?php

declare(strict_types=1);

namespace Pollen\Filesystem;

use League\Flysystem\FilesystemOperator;
use League\Flysystem\Local\LocalFilesystemAdapter as BaseLocalFilesystemAdapter;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\UnixVisibility\VisibilityConverter;
use Pollen\Filesystem\Drivers\LocalDriver;
use Pollen\Filesystem\Drivers\LocalFilesystem;
use Pollen\Filesystem\Drivers\LocalFilesystemAdapter;
use Pollen\Filesystem\Drivers\LocalImageDriver;
use Pollen\Filesystem\Drivers\LocalImageFilesystem;
use Pollen\Filesystem\Drivers\S3Driver;
use Pollen\Support\Concerns\BootableTrait;
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
    use BootableTrait;
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
     * List of registered filesystem driver instances.
     * @var array<string, FilesystemDriver|callable>|array
     */
    private array $drivers = [];

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

        $this->boot();
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
     * @inheritDoc
     */
    public function boot(): StorageManagerInterface
    {
        if (!$this->isBooted()) {
            $this->drivers = [
                'local'       => new LocalDriver(null, $this),
                'local-image' => new LocalImageDriver(null, $this),
                's3'          => new S3Driver(null, $this),
            ];

            $this->setBooted();
        }
        return $this;
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
    public function createLocalAdapter(string $root, array $config = []): LocalFilesystemAdapter
    {
        $visibility = $config['visibility'] ?? null;
        if (!$visibility instanceof VisibilityConverter) {
            $visibility = is_array($visibility) ? PortableVisibilityConverter::fromArray($visibility) : null;
        }

        $writeFlags = (int)($config['write_flags'] ?? LOCK_EX);

        $linkHandling = ($config['links'] ?? null) === 'skip'
            ? BaseLocalFilesystemAdapter::SKIP_LINKS : BaseLocalFilesystemAdapter::DISALLOW_LINKS;

        return new LocalFilesystemAdapter($root, $visibility, $writeFlags, $linkHandling);
    }

    /**
     * @inheritDoc
     */
    public function createLocalFilesystem(string $root, array $config = []): LocalFilesystem
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
    public function localDisk(?string $name = null): ?LocalFilesystem
    {
        if (($disk = $this->disk($name)) && $disk instanceof LocalFilesystem) {
            return $disk;
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function localImageDisk(?string $name = null): ?LocalImageFilesystem
    {
        if (($disk = $this->disk($name)) && $disk instanceof LocalImageFilesystem) {
            return $disk;
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function registerDisk(string $diskName, string $driverName, ...$adapterArgs): FilesystemOperator
    {
        $driver = $this->drivers[$driverName];

        $disk = $driver(...$adapterArgs);

        $this->addDisk($diskName, $disk);

        return $disk;
    }

    /**
     * @inheritDoc
     */
    public function registerDriver(string $name, FilesystemDriverInterface $driver): StorageManagerInterface
    {
        $this->drivers[$name] = $driver;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function registerLocalDisk(string $name, string $root, ?array $config = null): LocalFilesystem
    {
        $driver = $this->drivers['local'];

        $disk = $driver($root, $config);

        $this->addDisk($name, $disk);

        if ($disk instanceof LocalFilesystem) {
            return $disk;
        }
        throw new RuntimeException(sprintf('StorageManager unable to register local disk [%s].', $name));
    }

    /**
     * @inheritDoc
     */
    public function registerLocalImageDisk(
        string $name,
        string $root,
        ?array $config = null
    ): LocalImageFilesystem {
        $driver = $this->drivers['local-image'];

        $disk = $driver($root, $config);

        $this->addDisk($name, $disk);

        if ($disk instanceof LocalImageFilesystem) {
            return $disk;
        }
        throw new RuntimeException(sprintf('StorageManager unable to register local image disk [%s].', $name));
    }

    /**
     * @inheritDoc
     */
    public function registerS3Disk(
        string $name,
        array $client,
        string $bucket,
        ?array $config = null
    ): FilesystemInterface {
        $driver = $this->drivers['s3'];

        $disk = $driver($client, array_merge(is_array($config) ? $config : [], compact('bucket')));

        $this->addDisk($name, $disk);

        return $disk;
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