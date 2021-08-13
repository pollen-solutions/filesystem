<?php

declare(strict_types=1);

namespace Pollen\Filesystem;

use SplFileInfo;

interface LocalFilesystemAdapterInterface extends FilesystemAdapterInterface
{
    /**
     * Gets the absolute path of a resource from its path.
     *
     * @param string $path
     *
     * @return string
     */
    public function getAbsolutePath(string $path = '/'): string;

    /**
     * Gets the SplFileInfo instance of a resource from its path.
     *
     * @param string $path
     *
     * @return SplFileInfo
     */
    public function getSplFileInfo(string $path = '/'): SplFileInfo;
}