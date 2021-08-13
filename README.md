# Pollen Filesystem Component

[![Latest Version](https://img.shields.io/badge/release-1.0.0-blue?style=for-the-badge)](https://www.presstify.com/pollen-solutions/filesystem/)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-green?style=for-the-badge)](LICENSE.md)
[![PHP Supported Versions](https://img.shields.io/badge/PHP->=7.4-8892BF?style=for-the-badge&logo=php)](https://www.php.net/supported-versions.php)

Pollen **Filesystem** Component is an abstraction layer of file storage
library [Flysystem](https://flysystem.thephpleague.com/v2/docs/).

## Installation

```bash
composer require pollen-solutions/filesystem
```

## Basic Usage

```php
use Pollen\Filesystem\StorageManager;

$storage = new StorageManager();

try {
    $listing = $storage->listContents('/');

    /** @var \League\Flysystem\StorageAttributes $item */
    foreach ($listing as $item) {
        $path = $item->path();

        if ($item instanceof \League\Flysystem\FileAttributes) {
            var_dump($path);
        } elseif ($item instanceof \League\Flysystem\DirectoryAttributes) {
            var_dump($path);
        }
    }
} catch (\League\Flysystem\FilesystemException $e) {
    var_dump($e->getMessage());
}

```

## API

The API of the pollen solutions filesystem is identical to that of Flysystem which it inherits. 
More information
on [Flysystem online official documentation](https://flysystem.thephpleague.com/v2/docs/usage/filesystem-api/).

Storage Manager inherits the Filesystem instance's methods from its default disk and provides other methods to access
other declared disk instances.

```php
use Pollen\Filesystem\StorageManagerInterface;

/** @var StorageManagerInterface $storage */

// Gets a filesystem instance provided by the storage manager from its name identifier.
$storage->disk('my-own-disk');

// Writing files in default filesystem.
try {
    $storage->write($path, $contents, $config);
} catch (\League\Flysystem\FilesystemException | \League\Flysystem\UnableToWriteFile $exception) {
    // handle the error
}

// Reading files in default filesystem.
try {
    $response = $storage->read($path);
} catch (FilesystemException | UnableToReadFile $exception) {
    // handle the error
}

// And more ...
// @see https://flysystem.thephpleague.com/v2/docs/usage/filesystem-api/
```

## Sets a custom default filesystem

The default disk is created from public dir of the web application, it accessible via native PHP
function [getcwd](https://www.php.net/manual/function.getcwd.php). But the risk is that you could delete, overwrite ...
files essential to the functioning of your application.

For this security convenients, it is preferable to config your own default fallback disk with better file permissions.

```php
use Pollen\Filesystem\StorageManagerInterface;

/** @var StorageManagerInterface $storage */
$defaultDisk = $storage->createLocalFilesystem('/my/secured/fallback/directory/absolute_path');

$storage->setDefaultDisk($defaultDisk);
```

## Register a local disk

### From a local dir path

```php
use Pollen\Filesystem\StorageManagerInterface;

/** @var StorageManagerInterface $storage */
$storage->registerLocalDisk('my-local-disk', '/my/secured/fallback/directory/absolute_path');
```

### From a custom local filesystem instance

```php
use Pollen\Filesystem\LocalFilesystem;
use Pollen\Filesystem\StorageManagerInterface;

/** @var StorageManagerInterface $storage */
$filesystem = new LocalFilesystem($storage->createLocalAdapter('/my/secured/fallback/directory/absolute_path'));

$storage->addLocalDisk('my-local-disk', $filesystem);
```

### From a custom local filesystem instance with custom adapter

More information about filesystem architecture
on [Flysystem online official documentation](https://flysystem.thephpleague.com/v2/docs/architecture/).

```php
use Pollen\Filesystem\LocalFilesystemAdapter;
use Pollen\Filesystem\LocalFilesystem;
use Pollen\Filesystem\StorageManagerInterface;

/** @var StorageManagerInterface $storage */
$adapter = new LocalFilesystemAdapter('/my/secured/fallback/directory/absolute_path');
$filesystem = new LocalFilesystem($adapter);

$storage->addLocalDisk('my-local-disk', $localDisk);
```

## Extended Local Filesystem API.

One of the main advantages of using Pollen Solutions Filesystem instead Flysystem, is that it is coupled with a system
of HTTP request. This allows to extend the API of Flystem with some practical features.

```php
use Pollen\Filesystem\StorageManagerInterface;

/** @var StorageManagerInterface $storage */
$disk = $storage->registerLocalDisk('my-local-disk', '/my/secured/fallback/directory/absolute_path');

// Returns the HTTP response to download a file.
$disk->downloadResponse('/sample.txt');

// Returns the HTTP binary file response to download a file.
$disk->binaryFileResponse('/sample.txt');

// Returns the url of a file or a directory.
$disk->getUrl('/sample.txt')

// Gets the absolute path of a file or a directory.
$disk->getAbsolutePath('/sample.txt');

// Gets the SplFileInfo instance of a file or a directory.
$disk->getSplFileInfo('/sample.txt');

// Gets the storage file attributes of a file or a directory.
$disk->getStorageAttributes('/sample.txt');
```

## Local Image Filesystem

Pollen Solutions Filesystem provides a specific Filesystem and its adapter for the local image files.

### Register a local image filesystem

```php
use Pollen\Filesystem\StorageManagerInterface;

/** @var StorageManagerInterface $storage */
$filesystem = $storage->registerLocalImageDisk('my-image-disk', '/my/image/directory/absolute_path'));
```

### Create a custom local image filesystem instance

```php
use Pollen\Filesystem\LocalImageFilesystem;
use Pollen\Filesystem\StorageManagerInterface;

/** @var StorageManagerInterface $storage */
$filesystem = new LocalImageFilesystem($storage->createLocalAdapter('/my/image/directory/absolute_path'));

$storage->addLocalDisk('my-image-disk', $filesystem);
```

## Local Image Filesystem extended API

```php
use Pollen\Filesystem\StorageManagerInterface;

/** @var StorageManagerInterface $storage */
$disk = $storage->registerLocalImageDisk('my-image-disk', '/my/image/directory/absolute_path'));

// Gets the HTML render of an image file from its path.
$disk->HtmlRender('/sample.jpg');

// Gets the image file url from its path.
$disk->getImgSrc('/sample.jpg');
```

## Advanced Usage

### Custom Filesystem and Adapter

Flysystem has a variety of Filesystems and adapters, sometimes provided by third party library.

Examples :

- [Aws S3 (v3) Adapter](https://flysystem.thephpleague.com/v2/docs/adapter/aws-s3-v3/)
- [Gitlab storage](https://github.com/RoyVoetman/flysystem-gitlab-storage)
- ...

You might need to use one of them and implement it in Pollen Solutions Filesystem Component.

This example uses Flysystem FTP Adapter. First of all, Flysystem FTP Adapter requires a new dependency installation.

```bash
composer require league/flysystem-ftp:^2.0
```

```php
use Pollen\Filesystem\Filesystem;
use Pollen\Filesystem\StorageManagerInterface;

// The internal adapter
$adapter = new \League\Flysystem\Ftp\FtpAdapter(
    // Connection options
    \League\Flysystem\Ftp\FtpConnectionOptions::fromArray([
        'host' => 'hostname', // required
        'root' => '/root/path/', // required
        'username' => 'username', // required
        'password' => 'password', // required
        'port' => 21,
        'ssl' => false,
        'timeout' => 90,
        'utf8' => false,
        'passive' => true,
        'transferMode' => FTP_BINARY,
        'systemType' => null, // 'windows' or 'unix'
        'ignorePassiveAddress' => null, // true or false
        'timestampsOnUnixListingsEnabled' => false, // true or false
        'recurseManually' => true // true 
    ])
);

$filesystem = new Filesystem($adapter);

/** @var StorageManagerInterface $storage */
$storage->addDisk('ftp-disk', $filesystem);

if ($disk = $storage->disk('ftp-disk')) {
    try {
        $listing = $disk->listContents('/');
    
        /** @var \League\Flysystem\StorageAttributes $item */
        foreach ($listing as $item) {
            $path = $item->path();
    
            if ($item instanceof \League\Flysystem\FileAttributes) {
                var_dump($path);
            } elseif ($item instanceof \League\Flysystem\DirectoryAttributes) {
                var_dump($path);
            }
        }
    } catch (\League\Flysystem\FilesystemException $e) {
        var_dump($e->getMessage());
    }
}
```

