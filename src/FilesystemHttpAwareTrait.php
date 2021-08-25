<?php

declare(strict_types=1);

namespace Pollen\Filesystem;

use League\Flysystem\FilesystemException;
use Pollen\Http\BinaryFileResponse;
use Pollen\Http\BinaryFileResponseInterface;
use Pollen\Http\UrlHelper;
use Pollen\Http\StreamedResponse;
use Pollen\Http\StreamedResponseInterface;
use Pollen\Support\DateTime;
use Pollen\Support\Proxy\HttpRequestProxy;
use Pollen\Support\Str;
use RuntimeException;
use Throwable;

trait FilesystemHttpAwareTrait
{
    use HttpRequestProxy;

    /**
     * Url to the root filesystem directory.
     * @var string|null
     */
    protected ?string $baseUrl = null;

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
    ): BinaryFileResponseInterface {
        try {
            $this->fileExists($path);

            $absolutePath = $this->adapter->getAbsolutePath($path);
            BinaryFileResponse::trustXSendfileTypeHeader();
            $response = new BinaryFileResponse($absolutePath);
            $filename = $name ?? basename($path);

            $disposition = $response->headers->makeDisposition('inline', $filename, Str::ascii($name));

            $response->headers->replace(
                [
                    'Content-Type'        => $this->mimeType($path),
                    'Content-Length'      => $this->fileSize($path),
                    'Content-Disposition' => $disposition,
                ] + $headers
            );

            $response->setCache(
                array_merge(
                    [
                        'last_modified' => (new DateTime())->setTimestamp($this->lastModified($path)),
                        's_maxage'      => $expires,
                    ],
                    $cache
                )
            );

            /** @var \DateTimeInterface $expiration */
            $expiration = (new DateTime())->modify("+$expires seconds");

            $response->setExpires($expiration);
        } catch (FilesystemException $e) {
            throw new RuntimeException(
                sprintf(
                    'FilesystemHttp binary response for path [%s] throws an exception : %s.', $path, $e->getMessage()
                )
            );
        }
        return $response;
    }

    /**
     * Try to determine url to the root of the filesystem.
     *
     * @return string|null
     */
    protected function determineBaseUrl(): ?string
    {
        try {
            $absolutePath = $this->adapter->getAbsolutePath();
        } catch (Throwable $e) {
            return null;
        }

        $request = $this->httpRequest();
        $documentRoot = $request->getDocumentRoot();

        if (!preg_match('/^' . preg_quote($documentRoot, '/') . '(.*)/', $absolutePath, $matches)) {
            return null;
        }

        return (new UrlHelper($request))->getAbsoluteUrl($matches[1]);
    }

    /**
     * Returns the HTTP response to download a file from its path.
     *
     * @param string $path
     * @param string|null $name
     * @param array|null $headers
     *
     * @return StreamedResponseInterface
     */
    public function downloadResponse(string $path, ?string $name = null, array $headers = []): StreamedResponseInterface
    {
        return $this->response($path, $name, $headers, 'attachment');
    }

    /**
     * Gets a resource url from its path.
     *
     * @param string $path
     *
     * @return string|null
     */
    public function getUrl(string $path): ?string
    {
        if ($this->baseUrl === null) {
            $this->baseUrl = $this->determineBaseUrl();
        }

        if ($this->baseUrl !== null) {
            return sprintf('%s/%s', rtrim($this->baseUrl, '/'), ltrim($path, '/'));
        }

        return null;
    }

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
    ): StreamedResponseInterface {
        try {
            $this->fileExists($path);

            $response = new StreamedResponse();
            $filename = $name ?? basename($path);

            $disposition = $response->headers->makeDisposition($disposition, $filename, Str::ascii($name ?: $filename));
            $response->headers->replace(
                [
                    'Content-Type'        => $this->mimeType($path),
                    'Content-Length'      => $this->fileSize($path),
                    'Content-Disposition' => $disposition,
                ] + $headers
            );

            $response->setCallback(
                function () use ($path) {
                    $stream = $this->readStream($path);

                    if (ftell($stream) !== 0) {
                        rewind($stream);
                    }
                    fpassthru($stream);
                    fclose($stream);
                }
            );

            return $response;
        } catch (FilesystemException $e) {
            throw new RuntimeException(
                sprintf('FilesystemHttp response for path [%s] throws an exception : %s.', $path, $e->getMessage())
            );
        }
    }

    /**
     * Sets the url of the root of filesystem.
     *
     * @param string $baseUrl
     *
     * @return void
     */
    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = $baseUrl;
    }
}