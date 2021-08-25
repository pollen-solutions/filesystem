<?php

declare(strict_types=1);

namespace Pollen\Filesystem\Drivers;

use League\Flysystem\Local\LocalFilesystemAdapter as BaseLocalFilesystemAdapter;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\UnixVisibility\VisibilityConverter;
use Pollen\Filesystem\FilesystemDriver;
use RuntimeException;

class LocalDriver extends FilesystemDriver
{
    /**
     * @var string|null
     */
    protected ?string $adapterDefinition = LocalFilesystemAdapter::class;

    /**
     * @var string
     */
    protected string $filesystemDefinition = LocalFilesystem::class;

    /**
     * @inheritDoc
     */
    public function parseArgs(...$args): array
    {
        if (!isset($args[0])) {
            throw new RuntimeException('Local Filesystem root directory is required.');
        } elseif (!is_string($args[0])) {
            throw new RuntimeException('Local Filesystem root directory could be a string.');
        }

        $_args = [$args[0]];

        if (isset($args[1]) && is_array($args[1]) && !empty($args[1])) {
            $config = $args[1];

            $visibility = $config['visibility'] ?? null;
            if ($visibility instanceof VisibilityConverter) {
                $_args[] = $visibility;
            } else {
                $_args[] = is_array($visibility)? PortableVisibilityConverter::fromArray($visibility) : null;
            }

            $_args[] = (int)($config['write_flags'] ?? LOCK_EX);

            $_args[] = ($config['links'] ?? null) === 'skip'
                ? BaseLocalFilesystemAdapter::SKIP_LINKS : BaseLocalFilesystemAdapter::DISALLOW_LINKS;
        } else {
            $_args[] = $args[1] ?? null;
            $_args[] = $args[2] ?? LOCK_EX;
            $_args[] = $args[3] ?? BaseLocalFilesystemAdapter::DISALLOW_LINKS;
            $_args[] = $args[4] ?? null;
        }

        return $_args;
    }
}