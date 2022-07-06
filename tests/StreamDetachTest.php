<?php

declare(strict_types=1);

namespace HNV\Http\StreamTests;

use HNV\Http\Stream\Stream;

/**
 * PSR-7 StreamInterface implementation test.
 *
 * Testing stream detaching behavior.
 *
 * @internal
 * @covers Stream
 * @small
 */
class StreamDetachTest extends AbstractStreamTest
{
    /**
     * @covers          Stream::detach
     * @dataProvider    dataProviderResources
     *
     * @param resource $resource recourse
     */
    public function testDetach($resource): void
    {
        $stream = new Stream($resource);

        static::assertSame(
            $resource,
            $stream->detach(),
            "Action \"Stream->detach\" returned unexpected result.\n".
            "Expected result is \"THE SAME resource\".\n".
            'Caught result is "NOT THE SAME resource".'
        );
    }

    /**
     * @covers          Stream::detach
     * @dataProvider    dataProviderResources
     *
     * @param resource $resource recourse
     */
    public function testDetachOnClosedStream($resource): void
    {
        $stream = new Stream($resource);
        $stream->close();

        static::assertNull(
            $stream->detach(),
            "Action \"Stream->close->detach\" returned unexpected result.\n".
            "Expected result is \"null\".\n".
            'Caught result is "NOT null".'
        );
    }

    /**
     * @covers          Stream::detach
     * @dataProvider    dataProviderResources
     *
     * @param resource $resource recourse
     */
    public function testDetachOnDetachedStream($resource): void
    {
        $stream = new Stream($resource);
        $stream->detach();

        static::assertNull(
            $stream->detach(),
            "Action \"Stream->detach->detach\" returned unexpected result.\n".
            "Expected result is \"null\".\n".
            'Caught result is "NOT null".'
        );
    }
}
