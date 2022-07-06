<?php

declare(strict_types=1);

namespace HNV\Http\StreamTests;

use HNV\Http\Helper\Collection\Resource\AccessModeType;
use HNV\Http\Helper\Generator\Text as TextGenerator;
use HNV\Http\Stream\Stream;

use function array_keys;
use function fseek;
use function fwrite;
use function rewind;
use function stream_get_meta_data;
use function strlen;
use function var_export;

/**
 * PSR-7 StreamInterface implementation test.
 *
 * Testing stream metadata info work with.
 *
 * @internal
 * @covers Stream
 * @small
 */
class StreamGetMetadataTest extends AbstractStreamTest
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
     * @covers          Stream::getMetadata
     * @dataProvider    dataProviderResourcesWithMetadata
     *
     * @param resource $resource resource
     */
    public function testGetMetadata($resource, array $metadataExpected): void
    {
        $metadataCaught             = (new Stream($resource))->getMetadata();
        $metadataExpectedPrintable  = var_export($metadataExpected, true);
        $metadataCaughtPrintable    = var_export($metadataCaught, true);

        static::assertSame(
            $metadataExpected,
            $metadataCaught,
            "Action \"Stream->getMetadata\" returned unexpected result.\n".
            "Expected result is \"{$metadataExpectedPrintable}\".\n".
            "Caught result is \"{$metadataCaughtPrintable}\"."
        );
    }

    /**
     * @covers          Stream::getMetadata
     * @dataProvider    dataProviderResources
     *
     * @param resource $resource resource
     */
    public function testGetMetadataInClosedState($resource): void
    {
        $stream = new Stream($resource);
        $stream->close();

        static::assertNull(
            $stream->getMetadata(),
            "Action \"Stream->close->getMetadata\" returned unexpected result.\n".
            "Expected result is \"null\".\n".
            'Caught result is "NOT null".'
        );
    }

    /**
     * @covers          Stream::getMetadata
     * @dataProvider    dataProviderResources
     *
     * @param resource $resource resource
     */
    public function testGetMetadataInDetachedState($resource): void
    {
        $stream = new Stream($resource);
        $stream->detach();

        static::assertNull(
            $stream->getMetadata(),
            "Action \"Stream->detach->getMetadata\" returned unexpected result.\n".
            "Expected result is \"null\".\n".
            'Caught result is "NOT null".'
        );
    }

    /**
     * @covers          Stream::getMetadata
     * @dataProvider    dataProviderResourcesWithMetadataByKey
     *
     * @param resource $resource resource
     */
    public function testGetMetadataByKey($resource, string $key, mixed $metadataExpected): void
    {
        $metadataCaught             = (new Stream($resource))->getMetadata($key);
        $metadataExpectedPrintable  = var_export($metadataExpected, true);
        $metadataCaughtPrintable    = var_export($metadataCaught, true);

        static::assertSame(
            $metadataExpected,
            $metadataCaught,
            "Action \"Stream->getMetadata\" returned unexpected result.\n".
            "Action was called with parameters (key => {$key}).\n".
            "Expected result is \"{$metadataExpectedPrintable}\".\n".
            "Caught result is \"{$metadataCaughtPrintable}\"."
        );
    }

    /**
     * @covers          Stream::getMetadata
     * @dataProvider    dataProviderResources
     *
     * @param resource $resource resource
     */
    public function testGetMetadataByKeyInClosedState($resource): void
    {
        $stream = new Stream($resource);
        $stream->close();

        foreach (self::METADATA_DEFAULT_VALUES as $key => $valueExpected) {
            $valueCaught = $stream->getMetadata($key);

            static::assertSame(
                $valueExpected,
                $valueCaught,
                "Action \"Stream->close->getMetadata\" returned unexpected result.\n".
                "Action was called with parameters (key => {$key}).\n".
                "Expected result is \"{$valueExpected}\".\n".
                "Caught result is \"{$valueCaught}\"."
            );
        }
    }

    /**
     * @covers          Stream::getMetadata
     * @dataProvider    dataProviderResources
     *
     * @param resource $resource resource
     */
    public function testGetMetadataByKeyInDetachedState($resource): void
    {
        $stream = new Stream($resource);
        $stream->detach();

        foreach (self::METADATA_DEFAULT_VALUES as $key => $metadataExpected) {
            $metadataCaught = $stream->getMetadata($key);

            static::assertSame(
                $metadataExpected,
                $metadataCaught,
                "Action \"Stream->detach->getMetadata\" returned unexpected result.\n".
                "Action was called with parameters (key => {$key}).\n".
                "Expected result is \"{$metadataExpected}\".\n".
                "Caught result is \"{$metadataCaught}\"."
            );
        }
    }

    /**
     * Data provider: resources with metadata.
     */
    public function dataProviderResourcesWithMetadata(): array
    {
        $result = [];

        foreach ($this->generateResources(AccessModeType::ALL) as $resource) {
            $result[] = [$resource, stream_get_meta_data($resource)];
        }
        foreach ($this->generateResources(AccessModeType::READABLE_AND_WRITABLE) as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            $result[]   = [$resource, stream_get_meta_data($resource)];
        }
        foreach ($this->generateResources(AccessModeType::READABLE_AND_WRITABLE) as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            rewind($resource);
            $result[]   = [$resource, stream_get_meta_data($resource)];
        }
        foreach ($this->generateResources(AccessModeType::READABLE_AND_WRITABLE) as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            fseek($resource, (int) (strlen($content) / 2));
            $result[]   = [$resource, stream_get_meta_data($resource)];
        }

        return $result;
    }

    /**
     * Data provider: resources with metadata by key.
     */
    public function dataProviderResourcesWithMetadataByKey(): array
    {
        $availableKeys      = array_keys(self::METADATA_DEFAULT_VALUES);
        $unavailableKeys    = [
            'someKey1',
            'someKey2',
            'someKey3',
        ];
        $result             = [];

        foreach ($availableKeys as $key) {
            foreach ($this->dataProviderResourcesWithMetadata() as $resourceWithMetadata) {
                $resource   = $resourceWithMetadata[0];
                $paramValue = $resourceWithMetadata[1][$key] ?? null;
                $result[]   = [$resource, $key, $paramValue];
            }
        }
        foreach ($unavailableKeys as $key) {
            foreach ($this->dataProviderResourcesWithMetadata() as $resourceWithMetadata) {
                $resource   = $resourceWithMetadata[0];
                $result[]   = [$resource, $key, null];
            }
        }

        return $result;
    }
}
