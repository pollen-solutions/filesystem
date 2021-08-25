<?php

declare(strict_types=1);

namespace Pollen\Filesystem\Drivers;

use Exception;
use League\Flysystem\FilesystemException;
use League\MimeTypeDetection\ExtensionMimeTypeDetector;
use Pollen\Support\Html;
use RuntimeException;

class LocalImageFilesystem extends LocalFilesystem
{
    /**
     * @inheritDoc
     */
    public function __invoke(string $path, array $attrs = []): ?string
    {
        return $this->htmlRender($path, $attrs);
    }

    /**
     * Gets the HTML render of an image file from its path.
     *
     * @param string $path
     * @param array|null $attrs List of HTML tag attributes.
     *
     * @return string
     */
    public function htmlRender(string $path, ?array $attrs = null): ?string
    {
        try {
            $content = $this->read($path);
            $mimeType = $this->mimeType($path);
            $filename = $this->adapter->getAbsolutePath($path);

            if (!is_file($filename)) {
                throw new RuntimeException(
                    sprintf('HTML Render works only with files, path [%s] is not a valid file', $path)
                );
            }

            if (!preg_match('/^image\/(.*)/', $mimeType, $mimes)) {
                throw new RuntimeException(
                    sprintf('HTML Render works only with image file, path [%s] is not a valid Mime Type', $path)
                );
            }

            if (preg_match('/^svg\+?/', $mimes[1])) {
                return $attrs === null ? $content : "<div " . Html::attr($attrs) . ">$content</div>";
            }

            $attrs = $attrs ?? [];
            try {
                $attrs['src'] = $this->getImgSrc($path);
            } catch(Exception $e) {
                return null;
            }

            if (!isset($attrs['alt'])) {
                $attrs['alt'] = basename($filename, '.' . pathinfo($filename, PATHINFO_EXTENSION));
            }

            return "<img " . Html::attr($attrs) . "/>";
        } catch (FilesystemException $e) {
            throw new RuntimeException(
                sprintf('HTML Render method call provides an exception : %s.', $e->getMessage())
            );
        }
    }

    /**
     * Gets the image file url from its path.
     *
     * @param string $path
     * @param bool $forceBase64
     *
     * @return string
     */
    public function getImgSrc(string $path, bool $forceBase64 = false): string
    {
        try {
            if (!$this->fileExists($path)) {
                throw new RuntimeException(
                    sprintf('File located in [%s] does not exists.', $path)
                );
            }

            $content = $this->read($path);
            $filename = $this->adapter->getAbsolutePath($path);
            $mimeType = (new ExtensionMimeTypeDetector())->detectMimeTypeFromPath($filename);

            if (!preg_match('/^image\/(.*)/', $mimeType, $mimes)) {
                throw new RuntimeException(
                    sprintf('File located in [%s] must be of type image.', $path)
                );
            }

            $forceBase64 = $forceBase64 ?: (bool)preg_match('/^svg\+?/', $mimeType);
            $url = $forceBase64 ? null : $this->getUrl($path);

            if ($forceBase64 || $url === null) {
                return sprintf('data:%s;base64,%s', $mimeType, base64_encode($content));
            }

            return $url;
        } catch (FilesystemException $e) {
            throw new RuntimeException(
                sprintf('HTML Render method call provides an exception : %s.', $e->getMessage())
            );
        }
    }
}