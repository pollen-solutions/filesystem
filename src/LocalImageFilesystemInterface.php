<?php

declare(strict_types=1);

namespace Pollen\Filesystem;

interface LocalImageFilesystemInterface extends LocalFilesystemInterface
{
    /**
     * Gets the HTML render of an image file from its path.
     *
     * @param string $path
     * @param array|null $attrs List of HTML tag attributes.
     *
     * @return string
     */
    public function HtmlRender(string $path, ?array $attrs = []): ?string;

    /**
     * Gets the image file url from its path.
     *
     * @param string $path
     * @param bool $forceBase64
     *
     * @return string
     */
    public function getImgSrc(string $path, bool $forceBase64 = false): string;
}