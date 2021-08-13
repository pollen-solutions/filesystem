<?php

declare(strict_types=1);

namespace Pollen\Filesystem;

use League\Flysystem\Filesystem as BaseFilesystem;
use League\Flysystem\PathNormalizer;

/**
 * @mixin BaseFilesystem
 */
abstract class AbstractFilesystem extends Filesystem
{
    /**
     * Related filesystem adapter instance.
     * @var FilesystemAdapterInterface
     */
    protected FilesystemAdapterInterface $adapter;

    /**
     * @param FilesystemAdapterInterface $adapter
     * @param array $config
     * @param PathNormalizer|null $pathNormalizer
     */
    public function __construct(
        FilesystemAdapterInterface $adapter,
        array $config = [],
        PathNormalizer $pathNormalizer = null
    ) {
        parent::__construct($adapter, $config, $pathNormalizer);

        $this->adapter = $adapter;
    }
}