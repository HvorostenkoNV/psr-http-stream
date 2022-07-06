<?php

declare(strict_types=1);

namespace HNV\Http\StreamTests;

use HNV\Http\Helper\Collection\Resource\AccessModeType;
use HNV\Http\Helper\Generator\Text as TextGenerator;
use HNV\Http\Stream\Stream;
use RuntimeException;

use function fwrite;
use function rewind;
use function strlen;
use function substr;

/**
 * PSR-7 StreamInterface implementation test.
 *
 * Testing stream reading behavior.
 *
 * @internal
 * @covers Stream
 * @small
 */
class StreamReadTest extends AbstractStreamTest
{
    /**
     * @covers          Stream::read
     * @dataProvider    dataProviderResourcesWithReadParametersValid
     *
     * @param resource $resource resource
     */
    public function testRead($resource, int $length, string $contentExpected): void
    {
        $contentCaught = (new Stream($resource))->read($length);

        static::assertSame(
            $contentExpected,
            $contentCaught,
            "Action \"Stream->read\" returned unexpected result.\n".
            "Action was called with parameters (length => {$length}).\n".
            "Expected result is \"{$contentExpected}\".\n".
            "Caught result is \"{$contentCaught}\"."
        );
    }

    /**
     * @covers          Stream::read
     * @dataProvider    dataProviderResourcesWithReadParametersInvalid
     *
     * @param resource $resource resource
     */
    public function testReadThrowsException($resource, int $length): void
    {
        $this->expectException(RuntimeException::class);

        (new Stream($resource))->read($length);

        static::fail(
            "Action \"Stream->read\" threw no expected exception.\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }

    /**
     * @covers          Stream::read
     * @dataProvider    dataProviderResources
     *
     * @param resource $resource recourse
     */
    public function testReadInClosedState($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);
        $stream->close();
        $stream->read(0);

        static::fail(
            "Action \"Stream->close->read\" threw no expected exception.\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }

    /**
     * @covers          Stream::read
     * @dataProvider    dataProviderResources
     *
     * @param resource $resource recourse
     */
    public function testReadInDetachedState($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);
        $stream->detach();
        $stream->read(0);

        static::fail(
            "Action \"Stream->detach->read\" threw no expected exception.\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }

    /**
     * Data provider: resources with data to read valid value.
     */
    public function dataProviderResourcesWithReadParametersValid(): array
    {
        $result = [];

        foreach ($this->generateResources(AccessModeType::READABLE) as $resource) {
            $result[] = [$resource, 0, ''];
        }
        foreach ($this->generateResources(AccessModeType::READABLE_AND_WRITABLE) as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            rewind($resource);
            $result[]   = [$resource, strlen($content), $content];
        }
        foreach ($this->generateResources(AccessModeType::READABLE_AND_WRITABLE) as $resource) {
            $content            = (new TextGenerator())->generate();
            $readLength         = (int) (strlen($content) / 2);
            $expectedContent    = substr($content, 0, $readLength);
            fwrite($resource, $content);
            rewind($resource);
            $result[]           = [$resource, $readLength, $expectedContent];
        }
        foreach ($this->generateResources(AccessModeType::READABLE_AND_WRITABLE) as $resource) {
            $content    = (new TextGenerator())->generate();
            fwrite($resource, $content);
            $this->reachResourceEnd($resource);
            $result[]   = [$resource, strlen($content), ''];
        }

        return $result;
    }

    /**
     * Data provider: resources with data to read invalid value.
     */
    public function dataProviderResourcesWithReadParametersInvalid(): array
    {
        $result = [];

        foreach ($this->generateResources(AccessModeType::WRITABLE_ONLY) as $resource) {
            $result[] = [$resource, 1];
        }
        foreach ($this->generateResources(AccessModeType::READABLE_ONLY) as $resource) {
            $result[] = [$resource, -1];
        }

        return $result;
    }
}
