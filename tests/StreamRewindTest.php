<?php

declare(strict_types=1);

namespace HNV\Http\StreamTests;

use HNV\Http\Stream\Stream;
use RuntimeException;

use function ftell;

/**
 * PSR-7 StreamInterface implementation test.
 *
 * Testing stream rewinding behavior.
 *
 * @internal
 * @covers Stream
 * @small
 */
class StreamRewindTest extends AbstractStreamTest
{
    /**
     * @covers          Stream::rewind
     * @dataProvider    dataProviderResourcesSeekable
     *
     * @param resource $resource recourse
     */
    public function testRewind($resource): void
    {
        $stream = new Stream($resource);
        $stream->rewind();

        static::assertSame(
            0,
            ftell($resource),
            "Action \"Stream->rewind\" showed unexpected behavior.\n".
            "Expects underlying resource is \"seeked to the beginning\".\n".
            'Underlying resource is "NOT rewound".'
        );
    }

    /**
     * @covers          Stream::rewind
     * @dataProvider    dataProviderResources
     *
     * @param resource $resource recourse
     */
    public function testRewindInClosedState($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);
        $stream->close();
        $stream->rewind();

        static::fail(
            "Action \"Stream->close->rewind\" threw no expected exception.\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }

    /**
     * @covers          Stream::rewind
     * @dataProvider    dataProviderResources
     *
     * @param resource $resource recourse
     */
    public function testRewindInDetachedState($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);
        $stream->detach();
        $stream->rewind();

        static::fail(
            "Action \"Stream->detach->rewind\" threw no expected exception.\n".
            "Expects \"RuntimeException\" exception.\n".
            'Caught no exception.'
        );
    }

    /**
     * Data provider: seekable resources.
     */
    public function dataProviderResourcesSeekable(): array
    {
        return $this->dataProviderResources();
    }
}
