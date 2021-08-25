<?php

declare(strict_types=1);

namespace Pollen\Filesystem;

use League\Flysystem\FilesystemOperator;
use League\Flysystem\PathNormalizer;
use League\Flysystem\FilesystemAdapter;
use Pollen\Support\Proxy\ContainerProxy;
use Pollen\Support\Proxy\StorageProxy;
use Psr\Container\ContainerInterface as Container;
use RuntimeException;

class FilesystemDriver implements FilesystemDriverInterface
{
    use ContainerProxy;
    use StorageProxy;

    /**
     * @var string|null
     */
    protected ?string $adapterDefinition = null;

    /**
     * @var string
     */
    protected string $filesystemDefinition = Filesystem::class;

    /**
     * Filesystem configuration.
     * @var array
     */
    protected array $config = [];

    /**
     * Filesystem path normalizer instance.
     * @var PathNormalizer|null
     */
    protected ?PathNormalizer $pathNormalizer = null;

    /**
     * @param string|null $adapterDefinition
     * @param StorageManagerInterface|null $storageManager
     */
    public function __construct(?string $adapterDefinition = null, ?StorageManagerInterface $storageManager = null) {
        if ($adapterDefinition !== null) {
            $this->adapterDefinition = $adapterDefinition;
        }

        if ($storageManager !== null) {
            $this->setStorageManager($storageManager);
        }
    }

    /**
     * @inheritDoc
     */
    public function __invoke(...$args): FilesystemOperator
    {
        $_args = $this->parseArgs(...$args);

        if (!$adapter = $this->getAdapter(...$_args)) {
            throw new RuntimeException('FilesystemCreator unable to get the related FilesystemAdapter instance.');
        }

        return $this->getFilesystem($adapter);
    }

    /**
     * @inheritDoc
     */
    public function getFilesystem(FilesystemAdapter $adapter): FilesystemOperator
    {
        $fs = $this->filesystemDefinition ?? Filesystem::class;

        return new $fs($adapter, $this->config, $this->pathNormalizer);
    }

    /**
     * @inheritDoc
     */
    public function getAdapter(...$args): ?FilesystemAdapter
    {
        if ($adapter = $this->adapterDefinition) {
            return new $adapter(...$args);
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getContainer(): ?Container
    {
        return $this->container ?? $this->storageManager->getContainer();
    }

    /**
     * @inheritDoc
     */
    public function parseArgs(...$args): array
    {
        return $args;
    }

    /**
     * @inheritDoc
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setPathNormalizer(PathNormalizer $pathNormalizer): self
    {
        $this->pathNormalizer = $pathNormalizer;

        return $this;
    }
}