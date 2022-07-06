<?php

declare(strict_types=1);

namespace HNV\Http\StreamTests;

use HNV\Http\Helper\Collection\Resource\AccessModeType;
use HNV\Http\Helper\Generator\Text as TextGenerator;
use HNV\Http\Stream\Stream;

use function fwrite;
use function strlen;

/**
 * PSR-7 StreamInterface implementation test.
 *
 * Testing stream size info providing.
 *
 * @internal
 * @covers Stream
 * @small
 */
class StreamSizeTest extends AbstractStreamTest
{
    /**
     * @covers          Stream::getSize
     * @dataProvider    dataProviderResourcesWithSizeValue
     *
     * @param resource $resource recourse
     */
    public function testGetSize($resource, int $sizeExpected): void
    {
        $sizeCaught = (new Stream($resource))->getSize();

        static::assertSame(
            $sizeExpected,
            $sizeCaught,
            "Action \"Stream->getSize\" returned unexpected result.\n".
            "Expected result is \"{$sizeExpected}\".\n".
            "Caught result is \"{$sizeCaught}\"."
        );
    }

    /**
     * @covers          Stream::getSize
     * @dataProvider    dataProviderResourcesWithDataToWrite
     *
     * @param resource $resource recourse
     */
    public function testGetSizeInClosedState($resource, string $content): void
    {
        $stream = new Stream($resource);

        $stream->write($content);
        $stream->close();

        static::assertNull(
            $stream->getSize(),
            "Action \"Stream->write->close->getSize\" returned unexpected result.\n".
            "Expected result is \"null\".\n".
            'Caught result is "NOT null".'
        );
    }

    /**
     * @covers          Stream::getSize
     * @dataProvider    dataProviderResourcesWithDataToWrite
     *
     * @param resource $resource recourse
     */
    public function testGetSizeInDetachedState($resource, string $content): void
    {
        $stream = new Stream($resource);

        $stream->write($content);
        $stream->detach();

        static::assertNull(
            $stream->getSize(),
            "Action \"Stream->write->detach->getSize\" returned unexpected result.\n".
            "Expected result is \"null\".\n".
            'Caught result is "NOT null".'
        );
    }

    /**
     * Data provider: resources with data size.
     */
    public function dataProviderResourcesWithSizeValue(): array
    {
        $result = [];

        foreach ($this->generateResources(AccessModeType::READABLE_ONLY) as $resource) {
            $result[] = [$resource, 0];
        }
        foreach ($this->generateResources(AccessModeType::WRITABLE_ONLY) as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            $result[]   = [$resource, strlen($content)];
        }
        foreach ($this->generateResources(AccessModeType::READABLE_AND_WRITABLE) as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            $result[]   = [$resource, strlen($content)];
        }

        return $result;
    }

    /**
     * Data provider: resources with data to write.
     */
    public function dataProviderResourcesWithDataToWrite(): array
    {
        $result = [];

        foreach ($this->generateResources(AccessModeType::WRITABLE) as $resource) {
            $content    = (new TextGenerator())->generate();
            $result[]   = [$resource, $content];
        }

        return $result;
    }
}
