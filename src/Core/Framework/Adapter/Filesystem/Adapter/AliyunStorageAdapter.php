<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Adapter\Filesystem\Adapter;

use AlibabaCloud\Oss\V2\Client;
use AlibabaCloud\Oss\V2\Credentials\StaticCredentialsProvider;
use AlibabaCloud\Oss\V2\Models\CopyObjectRequest;
use AlibabaCloud\Oss\V2\Models\DeleteObjectRequest;
use AlibabaCloud\Oss\V2\Models\GetObjectAclRequest;
use AlibabaCloud\Oss\V2\Models\GetObjectMetaRequest;
use AlibabaCloud\Oss\V2\Models\GetObjectRequest;
use AlibabaCloud\Oss\V2\Models\ListObjectsV2Request;
use AlibabaCloud\Oss\V2\Models\ObjectACLType;
use AlibabaCloud\Oss\V2\Models\PutObjectAclRequest;
use AlibabaCloud\Oss\V2\Models\PutObjectRequest;
use AlibabaCloud\Oss\V2\Paginator\ListObjectsV2Paginator;
use AlibabaCloud\Oss\V2\Utils;
use League\Flysystem\Config;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToGenerateTemporaryUrl;
use League\Flysystem\UnableToListContents;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnableToWriteFile;
use League\Flysystem\UrlGeneration\TemporaryUrlGenerator;
use League\Flysystem\Visibility;

class AliyunStorageAdapter implements FilesystemAdapter, TemporaryUrlGenerator
{
    protected readonly Client $ossClient;

    public function __construct(
        string $key,
        string $secret,
        string $region,
        private readonly string $bucket,
    ) {
        $cfg = new \AlibabaCloud\Oss\V2\Config($region);
        $cfg->setCredentialsProvider(new StaticCredentialsProvider($key, $secret));
        $this->ossClient = new Client($cfg);
    }

    public function fileExists(string $path): bool
    {
        return $this->ossClient->isObjectExist($this->bucket, $path);
    }

    public function directoryExists(string $path): bool
    {
        return $this->ossClient->isObjectExist($this->bucket, $path);
    }

    public function write(string $path, string $contents, Config $config): void
    {
        $this->ossClient->putObject(new PutObjectRequest($this->bucket, $path, body: Utils::streamFor($contents)));
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        if (!\is_resource($contents)) {
            throw UnableToWriteFile::atLocation($path, 'writeStream requires a stream resource or StreamInterface');
        }
        $body = Utils::streamFor($contents);
        $this->ossClient->putObject(new PutObjectRequest(
            $this->bucket,
            $path,
            body: $body
        ));
    }

    public function read(string $path): string
    {
        try {
            $getObjectResult = $this->ossClient->getObject(new GetObjectRequest($this->bucket, $path));
            if ($getObjectResult->statusCode !== 200) {
                throw UnableToReadFile::fromLocation($path, "OSS returned status code {$getObjectResult->statusCode}");
            }

            return (string) $getObjectResult->body;
        } catch (\Exception $e) {
            throw UnableToReadFile::fromLocation($path, $e->getMessage(), $e);
        }
    }

    public function readStream(string $path)
    {
        try {
            $getObjectResult = $this->ossClient->getObject(new GetObjectRequest($this->bucket, $path));
            if ($getObjectResult->statusCode !== 200) {
                throw UnableToReadFile::fromLocation($path, "OSS returned status code {$getObjectResult->statusCode}");
            }

            $body = Utils::streamFor($getObjectResult->body);

            $stream = fopen('php://temp', 'r+');
            if ($stream === false) {
                throw UnableToReadFile::fromLocation($path, 'Failed to create temporary stream');
            }

            fwrite($stream, (string) $body);
            rewind($stream);

            return $stream;
        } catch (\Exception $e) {
            throw UnableToReadFile::fromLocation($path, $e->getMessage(), $e);
        }
    }

    public function delete(string $path): void
    {
        $this->ossClient->deleteObject(new DeleteObjectRequest($this->bucket, $path));
    }

    public function deleteDirectory(string $path): void
    {
        $prefix = rtrim($path, '/') . '/';

        try {
            $request = new ListObjectsV2Request($this->bucket);
            $request->prefix = $prefix;
            $paginator = new ListObjectsV2Paginator($this->ossClient);

            foreach ($paginator->iterPage($request) as $page) {
                foreach ($page->contents ?? [] as $object) {
                    $this->ossClient->deleteObject(
                        new DeleteObjectRequest($this->bucket, $object->key)
                    );
                }
            }
        } catch (\Exception $e) {
            throw UnableToWriteFile::atLocation($path, $e->getMessage(), $e);
        }
    }

    public function createDirectory(string $path, Config $config): void
    {
        $prefix = rtrim($path, '/') . '/';
        $dummyFile = $prefix . '.keep';

        try {
            $this->ossClient->putObject(new PutObjectRequest(
                $this->bucket,
                $dummyFile,
                body: Utils::streamFor('')
            ));
        } catch (\Exception $e) {
            throw UnableToWriteFile::atLocation($path, $e->getMessage(), $e);
        }
    }

    public function setVisibility(string $path, string $visibility): void
    {
        $acl = match ($visibility) {
            Visibility::PUBLIC => ObjectACLType::PUBLIC_READ,
            Visibility::PRIVATE => ObjectACLType::PRIVATE,
            default => ObjectACLType::DEFAULT,
        };
        try {
            $this->ossClient->putObjectAcl(new PutObjectAclRequest($this->bucket, $path, $acl));
        } catch (\Exception $e) {
            throw UnableToWriteFile::atLocation($path, $e->getMessage(), $e);
        }
    }

    public function visibility(string $path): FileAttributes
    {
        try {
            $acl = $this->ossClient->getObjectAcl(new GetObjectAclRequest($this->bucket, $path));

            return new FileAttributes($path, visibility: $acl->accessControlList->grant);
        } catch (\Exception $e) {
            throw UnableToRetrieveMetadata::visibility($path, $e->getMessage(), $e);
        }
    }

    public function mimeType(string $path): FileAttributes
    {
        try {
            $getObjectResult = $this->ossClient->getObject(new GetObjectRequest($this->bucket, $path));

            return new FileAttributes($path, mimeType: $getObjectResult->contentType ?? null);
        } catch (\Exception $e) {
            throw UnableToRetrieveMetadata::mimeType($path, $e->getMessage(), $e);
        }
    }

    public function lastModified(string $path): FileAttributes
    {
        try {
            $meta = $this->ossClient->getObjectMeta(new GetObjectMetaRequest($this->bucket, $path));

            return new FileAttributes($path, lastModified: $meta->lastModified?->getTimestamp());
        } catch (\Exception $e) {
            throw UnableToRetrieveMetadata::lastModified($path, $e->getMessage(), $e);
        }
    }

    public function fileSize(string $path): FileAttributes
    {
        try {
            $meta = $this->ossClient->getObjectMeta(new GetObjectMetaRequest($this->bucket, $path));

            return new FileAttributes($path, fileSize: $meta->contentLength ?? null);
        } catch (\Exception $e) {
            throw UnableToRetrieveMetadata::mimeType($path, $e->getMessage(), $e);
        }
    }

    public function listContents(string $path, bool $deep): iterable
    {
        $prefix = rtrim($path, '/') . '/';
        $delimiter = $deep ? '' : '/';

        try {
            $request = new ListObjectsV2Request(
                bucket: $this->bucket,
                delimiter: $delimiter,
                prefix: $prefix,
            );

            $result = $this->ossClient->listObjectsV2($request);

            $items = [];

            if (!empty($result->contents)) {
                foreach ($result->contents as $object) {
                    $items[] = new FileAttributes(
                        $object->key
                    );
                }
            }

            if (!$deep && !empty($result->commonPrefixes)) {
                foreach ($result->commonPrefixes as $prefixItem) {
                    $items[] = new DirectoryAttributes(rtrim($prefixItem->prefix, '/'));
                }
            }

            return $items;
        } catch (\Throwable $e) {
            throw UnableToListContents::atLocation($path, $deep, $e);
        }
    }

    public function move(string $source, string $destination, Config $config): void
    {
        $this->copy($source, $destination, $config);
        $this->delete($source);
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        $this->ossClient->copyObject(new CopyObjectRequest(
            $this->bucket,
            $destination,
            $this->bucket,
            $source
        ));
    }

    public function temporaryUrl(string $path, \DateTimeInterface $expiresAt, Config $config): string
    {
        try {
            $expires = $expiresAt->getTimestamp() - time();
            if ($expires <= 0) {
                throw UnableToGenerateTemporaryUrl::noGeneratorConfigured($path, 'Expiration time must be in the future.');
            }
            $result = $this->ossClient->presign(
                new GetObjectRequest($this->bucket, $path),
                ['expires' => $expires]
            );

            return $result->url;
        } catch (\Throwable $e) {
            throw UnableToGenerateTemporaryUrl::dueToError($path, $e);
        }
    }
}
