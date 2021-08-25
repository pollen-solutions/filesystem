<?php

declare(strict_types=1);

namespace Pollen\Filesystem\Drivers;

class LocalImageDriver extends LocalDriver
{
    /**
     * @var string
     */
    protected string $filesystemDefinition = LocalImageFilesystem::class;
}