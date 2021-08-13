<?php

declare(strict_types=1);

namespace Pollen\Filesystem;

use Pollen\Container\ServiceProvider;

class FilesystemServiceProvider extends ServiceProvider
{
    /**
     * @var string[]
     */
    protected $provides = [
        StorageManagerInterface::class,
    ];

    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->getContainer()->share(StorageManagerInterface::class, function () {
            return new StorageManager([], $this->getContainer());
        });
    }
}