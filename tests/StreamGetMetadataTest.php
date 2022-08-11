<?php

declare(strict_types=1);

namespace HNV\Http\StreamTests;

use HNV\Http\Helper\Collection\Resource\AccessModeType;
use HNV\Http\Helper\Generator\Text as TextGenerator;
use HNV\Http\Stream\Stream;
use PHPUnit\Framework\Attributes;

use function array_keys;
use function fseek;
use function fwrite;
use function rewind;
use function stream_get_meta_data;
use function strlen;

/**
 * @internal
 */
#[Attributes\CoversClass(Stream::class)]
#[Attributes\Small]
class StreamGetMetadataTest extends AbstractStreamTestCase
{
    private const METADATA_DEFAULT_VALUES = [
        'timed_out'     => true,
        'blocked'       => true,
        'eof'           => true,
        'unread_bytes'  => 0,
        'stream_type'   => '',
        'wrapper_type'  => '',
        'wrapper_data'  => '',
        'mode'          => '',
        'seekable'      => false,
        'uri'           => '',
    ];

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResourcesWithMetadata')]
    public function getMetadata($resource, array $metadataExpected): void
    {
        $metadataCaught = (new Stream($resource))->getMetadata();

        static::assertSame($metadataExpected, $metadataCaught);
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResources')]
    public function getMetadataOnClosedStream($resource): void
    {
        $stream = new Stream($resource);
        $stream->close();

        static::assertNull(
            $stream->getMetadata(),
            'Expects null on getting metadata of closed stream'
        );
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResources')]
    public function getMetadataOnDetachedStream($resource): void
    {
        $stream = new Stream($resource);
        $stream->detach();

        static::assertNull(
            $stream->getMetadata(),
            'Expects null on getting metadata of detached stream'
        );
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResourcesWithMetadataByKey')]
    public function getMetadataByKey($resource, string $key, mixed $metadataExpected): void
    {
        $metadataCaught = (new Stream($resource))->getMetadata($key);

        static::assertSame($metadataExpected, $metadataCaught);
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResources')]
    public function getMetadataByKeyOnClosedStream($resource): void
    {
        $stream = new Stream($resource);
        $stream->close();

        foreach (self::METADATA_DEFAULT_VALUES as $key => $valueExpected) {
            static::assertSame(
                $valueExpected,
                $stream->getMetadata($key),
                'Expects default value on getting metadata of closed stream'
            );
        }
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResources')]
    public function getMetadataByKeyOnDetachedStream($resource): void
    {
        $stream = new Stream($resource);
        $stream->detach();

        foreach (self::METADATA_DEFAULT_VALUES as $key => $valueExpected) {
            static::assertSame(
                $valueExpected,
                $stream->getMetadata($key),
                'Expects default value on getting metadata of detached stream'
            );
        }
    }

    public function dataProviderResourcesWithMetadata(): iterable
    {
        foreach ($this->generateResources(AccessModeType::ALL) as $resource) {
            yield [$resource, stream_get_meta_data($resource)];
        }

        foreach ($this->generateResources(AccessModeType::READABLE_AND_WRITABLE) as $resource) {
            $content = (new TextGenerator())->generate();
            fwrite($resource, $content);

            yield [$resource, stream_get_meta_data($resource)];
        }

        foreach ($this->generateResources(AccessModeType::READABLE_AND_WRITABLE) as $resource) {
            $content = (new TextGenerator())->generate();
            fwrite($resource, $content);
            rewind($resource);

            yield [$resource, stream_get_meta_data($resource)];
        }

        foreach ($this->generateResources(AccessModeType::READABLE_AND_WRITABLE) as $resource) {
            $content = (new TextGenerator())->generate();
            fwrite($resource, $content);
            fseek($resource, (int) (strlen($content) / 2));

            yield [$resource, stream_get_meta_data($resource)];
        }
    }

    public function dataProviderResourcesWithMetadataByKey(): iterable
    {
        $availableKeys      = array_keys(self::METADATA_DEFAULT_VALUES);
        $unavailableKeys    = [
            'someKey1',
            'someKey2',
            'someKey3',
        ];

        foreach ($availableKeys as $key) {
            foreach ($this->dataProviderResourcesWithMetadata() as $resourceWithMetadata) {
                $resource   = $resourceWithMetadata[0];
                $paramValue = $resourceWithMetadata[1][$key] ?? null;

                yield [$resource, $key, $paramValue];
            }
        }

        foreach ($unavailableKeys as $key) {
            foreach ($this->dataProviderResourcesWithMetadata() as $resourceWithMetadata) {
                $resource = $resourceWithMetadata[0];

                yield [$resource, $key, null];
            }
        }
    }
}
