<?php

declare(strict_types=1);

namespace Pollen\Filesystem;

use Pollen\Http\BinaryFileResponseInterface;
use Pollen\Http\StreamedResponseInterface;
use Pollen\Support\Proxy\HttpRequestProxyInterface;

interface FilesystemHttpAwareTraitInterface extends HttpRequestProxyInterface
{
    /**
     * Return the HTTP response of a binary file from its path.
     *
     * @param string $path
     * @param string|null $name
     * @param array $headers
     * @param int $expires
     * @param array $cache
     *
     * @return BinaryFileResponseInterface
     */
    public function binaryFileResponse(
        string $path,
        ?string $name = null,
        array $headers = [],
        int $expires = 31536000,
        array $cache = []
    ): BinaryFileResponseInterface;

    /**
     * Returns the HTTP response to download a file from its path.
     *
     * @param string $path
     * @param string|null $name
     * @param array|null $headers
     *
     * @return StreamedResponseInterface
     */
    public function downloadResponse(
        string $path,
        ?string $name = null,
        array $headers = []
    ): StreamedResponseInterface;

    /**
     * Gets a resource url from its path.
     *
     * @param string $path
     *
     * @return string|null
     */
    public function getUrl(string $path): ?string;

    /**
     * Returns the HTTP response of a file from its path.
     *
     * @param string $path
     * @param string|null $name
     * @param array|null $headers
     * @param string|null $disposition
     *
     * @return StreamedResponseInterface
     */
    public function response(
        string $path,
        ?string $name = null,
        array $headers = [],
        ?string $disposition = 'inline'
    ): StreamedResponseInterface;

    /**
     * Sets the url of the root of filesystem.
     *
     * @param string $baseUrl
     *
     * @return void
     */
    public function setBaseUrl(string $baseUrl): void;
}