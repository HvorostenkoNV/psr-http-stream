<?php
declare(strict_types=1);

namespace HNV\Http\StreamTests;

use Throwable;
use PHPUnit\Framework\TestCase;
use HNV\Http\StreamTests\Generator\{
    Resource\ReadableAndWritable    as ResourceGeneratorReadableAndWritable,
    Resource\All                    as ResourceGeneratorAll,
    Text                            as TextGenerator
};
use HNV\Http\Stream\Stream;

use function var_export;
use function strlen;
use function array_keys;
use function fwrite;
use function fseek;
use function rewind;
use function stream_get_meta_data;
/** ***********************************************************************************************
 * PSR-7 StreamInterface implementation test.
 *
 * Testing stream metadata info work with.
 *
 * @package HNV\Psr\Http\Tests\Stream
 * @author  Hvorostenko
 *************************************************************************************************/
class StreamGetMetadataTest extends TestCase
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
        'uri'           => ''
    ];
    /** **********************************************************************
     * Test "Stream::getMetadata" provides stream metadata as an associative array.
     *
     * @covers          Stream::getMetadata
     * @dataProvider    dataProviderResourcesWithMetadata
     *
     * @param           resource    $resource           Resource.
     * @param           array       $metadataExpected   Metadata expected.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testGetMetadata($resource, array $metadataExpected): void
    {
        $metadataCaught             = (new Stream($resource))->getMetadata();
        $metadataExpectedPrintable  = var_export($metadataExpected, true);
        $metadataCaughtPrintable    = var_export($metadataCaught, true);

        self::assertEquals(
            $metadataExpected,
            $metadataCaught,
            "Action \"Stream->getMetadata\" returned unexpected result.\n".
            "Expected result is \"$metadataExpectedPrintable\".\n".
            "Caught result is \"$metadataCaughtPrintable\"."
        );
    }
    /** **********************************************************************
     * Test "Stream::getMetadata" behavior with stream in a closed state.
     *
     * @covers          Stream::getMetadata
     * @dataProvider    dataProviderResources
     *
     * @param           resource $resource              Resource.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testGetMetadataInClosedState($resource): void
    {
        $stream = new Stream($resource);
        $stream->close();

        self::assertNull(
            $stream->getMetadata(),
            "Action \"Stream->close->getMetadata\" returned unexpected result.\n".
            "Expected result is \"null\".\n".
            "Caught result is \"NOT null\"."
        );
    }
    /** **********************************************************************
     * Test "Stream::getMetadata" behavior with stream in a detached state.
     *
     * @covers          Stream::getMetadata
     * @dataProvider    dataProviderResources
     *
     * @param           resource $resource              Resource.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testGetMetadataInDetachedState($resource): void
    {
        $stream = new Stream($resource);
        $stream->detach();

        self::assertNull(
            $stream->getMetadata(),
            "Action \"Stream->detach->getMetadata\" returned unexpected result.\n".
            "Expected result is \"null\".\n".
            "Caught result is \"NOT null\"."
        );
    }
    /** **********************************************************************
     * Test "Stream::getMetadata" provides metadata value by specific key.
     *
     * @covers          Stream::getMetadata
     * @dataProvider    dataProviderResourcesWithMetadataByKey
     *
     * @param           resource    $resource           Resource.
     * @param           string      $key                Specific key.
     * @param           mixed       $metadataExpected   Metadata expected.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testGetMetadataByKey($resource, string $key, mixed $metadataExpected): void
    {
        $metadataCaught             = (new Stream($resource))->getMetadata($key);
        $metadataExpectedPrintable  = var_export($metadataExpected, true);
        $metadataCaughtPrintable    = var_export($metadataCaught, true);

        self::assertEquals(
            $metadataExpected,
            $metadataCaught,
            "Action \"Stream->getMetadata\" returned unexpected result.\n".
            "Action was called with parameters (key => $key).\n".
            "Expected result is \"$metadataExpectedPrintable\".\n".
            "Caught result is \"$metadataCaughtPrintable\"."
        );
    }
    /** **********************************************************************
     * Test "Stream::getMetadata" provides metadata value by specific key
     * with stream in a closed state.
     *
     * @covers          Stream::getMetadata
     * @dataProvider    dataProviderResources
     *
     * @param           resource $resource              Resource.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testGetMetadataByKeyInClosedState($resource): void
    {
        $stream = new Stream($resource);
        $stream->close();

        foreach (self::METADATA_DEFAULT_VALUES as $key => $valueExpected) {
            $valueCaught = $stream->getMetadata($key);

            self::assertEquals(
                $valueExpected,
                $valueCaught,
                "Action \"Stream->close->getMetadata\" returned unexpected result.\n".
                "Action was called with parameters (key => $key).\n".
                "Expected result is \"$valueExpected\".\n".
                "Caught result is \"$valueCaught\"."
            );
        }
    }
    /** **********************************************************************
     * Test "Stream::getMetadata" provides metadata value by specific key
     * with stream in a detached state.
     *
     * @covers          Stream::getMetadata
     * @dataProvider    dataProviderResources
     *
     * @param           resource $resource              Resource.
     *
     * @return          void
     * @throws          Throwable
     ************************************************************************/
    public function testGetMetadataByKeyInDetachedState($resource): void
    {
        $stream = new Stream($resource);
        $stream->detach();

        foreach (self::METADATA_DEFAULT_VALUES as $key => $metadataExpected) {
            $metadataCaught = $stream->getMetadata($key);

            self::assertEquals(
                $metadataExpected,
                $metadataCaught,
                "Action \"Stream->detach->getMetadata\" returned unexpected result.\n".
                "Action was called with parameters (key => $key).\n".
                "Expected result is \"$metadataExpected\".\n".
                "Caught result is \"$metadataCaught\"."
            );
        }
    }
    /** **********************************************************************
     * Data provider: resources, readable and writable.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderResources(): array
    {
        $result = [];

        foreach ((new ResourceGeneratorAll())->generate() as $resource) {
            $result[] = [$resource];
        }

        return $result;
    }
    /** **********************************************************************
     * Data provider: resources with metadata.
     *
     * @return  array                                   Data.
     ************************************************************************/
    public function dataProviderResourcesWithMetadata(): array
    {
        $result = [];

        foreach ((new ResourceGeneratorAll())->generate() as $resource) {
            $result[] = [$resource, stream_get_meta_data($resource)];
        }
        foreach ((new ResourceGeneratorReadableAndWritable())->generate() as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            $result[]   = [$resource, stream_get_meta_data($resource)];
        }
        foreach ((new ResourceGeneratorReadableAndWritable())->generate() as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            rewind($resource);
            $result[]   = [$resource, stream_get_meta_data($resource)];
        }
        foreach ((new ResourceGeneratorReadableAndWritable())->generate() as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            fseek($resource, (int) (strlen($content) / 2));
            $result[]   = [$resource, stream_get_meta_data($resource)];
        }

        return $result;
    }
    /** **********************************************************************
     * Data provider: resources with metadata by key.
     *
     * @return  array                                   Data.
     ************************************************************************/
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