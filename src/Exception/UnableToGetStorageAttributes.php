<?php

declare(strict_types=1);

namespace Pollen\Filesystem\Exception;

use League\Flysystem\FilesystemException;
use RuntimeException;
use Throwable;

class UnableToGetStorageAttributes extends RuntimeException implements FilesystemException
{
    /**
     * @param string|null $path
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(?string $path = null, string $message = '', int $code = 0, Throwable $previous = null)
    {
        if (!$message) {
            $message = $path === null
                ? 'Filesystem Unable to get StorageAttributes.'
                : sprintf('Filesystem Unable to get StorageAttributes for path [%s].', $path);
        }

        parent::__construct($message, $code, $previous);
    }
}