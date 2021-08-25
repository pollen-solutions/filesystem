<?php

declare(strict_types=1);

namespace Pollen\Filesystem\Drivers;

use Aws\S3\S3Client;
use Aws\S3\S3ClientInterface;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\UnixVisibility\VisibilityConverter;
use Pollen\Filesystem\FilesystemDriver;
use Pollen\Filesystem\StorageManagerInterface;
use RuntimeException;

class S3Driver extends FilesystemDriver
{
    /**
     * @var string|null
     */
    protected ?string $adapterDefinition = AwsS3V3Adapter::class;

    public function __construct(
        ?string $adapterDefinition = null,
        ?StorageManagerInterface $storageManager = null
    ) {
        if (!class_exists(S3Client::class)) {
            throw new RuntimeException(
                'S3 Filesystem requires [%s], please install with : composer require aws/aws-sdk-php.',
                S3Client::class
            );
        }

        if (!class_exists(S3Client::class)) {
            throw new RuntimeException(
                'S3 Filesystem requires [%s], please install with : composer require league/flysystem-aws-s3-v3.',
                AwsS3V3Adapter::class
            );
        }

        parent::__construct($adapterDefinition, $storageManager);
    }

    /**
     * @inheritDoc
     */
    public function parseArgs(...$args): array
    {
        if (!isset($args[0]) && (!is_array($args[0]) || (!$args[0] instanceof S3ClientInterface))) {
            throw new RuntimeException(
                sprintf(
                    'S3 Filesystem requires array or %s client definition.',
                    S3ClientInterface::class
                )
            );
        }

        if (!isset($args[1])) {
            throw new RuntimeException(
                sprintf(
                    'S3 Filesystem requires at least one bucket string name or array of [%s] arguments.',
                    AwsS3V3Adapter::class
                )
            );
        }

        $_args = [];
        $_args[] = is_array($args[0]) ? new S3Client($args[0]) : $args[0];

        if(is_array($args[1])) {
            $config = $args[1];
            if (!isset($config['bucket'])) {
                throw new RuntimeException('Bucket name is requires by S3 Filesystem configuration parameters.');
            }

            $_args[] = $config['bucket'];
            $_args[] = $config['prefix'] ?? '';

            $visibility = $config['visibility'] ?? null;
            if ($visibility instanceof VisibilityConverter) {
                $_args[] = $visibility;
            } else {
                $_args[] = is_array($visibility)? PortableVisibilityConverter::fromArray($visibility) : null;
            }
        } elseif (is_string($args[1])) {
            $_args[] = $args[1];
            $_args[] = $args[2] ?? '';
            $_args[] = $args[3] ?? null;
            $_args[] = $args[4] ?? null;
            $_args[] = $args[5] ?? null;
            $_args[] = $args[6] ?? true;
        }

        return $_args;
    }
}