<?php

declare(strict_types=1);

namespace HNV\Http\StreamTests;

use HNV\Http\Stream\Stream;

use function is_resource;

/**
 * PSR-7 StreamInterface implementation test.
 *
 * Testing stream closing behavior.
 *
 * @internal
 * @covers Stream
 * @small
 */
class StreamCloseTest extends AbstractStreamTest
{
    /**
     * @covers          Stream::close
     * @dataProvider    dataProviderResources
     *
     * @param resource $resource recourse
     */
    public function testClose($resource): void
    {
        $stream = new Stream($resource);
        $stream->close();

        static::assertFalse(
            is_resource($resource),
            "Action \"Stream->close\" showed unexpected behavior.\n".
            "Expects underlying resource will be closed\n".
            'Expects underlying resource is not closed'
        );
    }

    /**
     * @covers          Stream::close
     * @dataProvider    dataProviderResources
     *
     * @param resource $resource recourse
     */
    public function testCloseOnDetachedResource($resource): void
    {
        $stream             = new Stream($resource);
        $resourceDetached   = $stream->detach();
        $stream->close();

        static::assertTrue(
            is_resource($resourceDetached),
            "Action \"Stream->detach->close\" showed unexpected behavior.\n".
            "Expects underlying resource will be NOT closed.\n".
            'Expects underlying resource is closed'
        );
    }

    /**
     * @covers          Stream::__destruct
     * @dataProvider    dataProviderResources
     *
     * @param resource $resource recourse
     */
    public function testDestructorClosesResource($resource): void
    {
        $stream = new Stream($resource);
        unset($stream);

        static::assertFalse(
            is_resource($resource),
            "Action \"Stream->__destruct\" showed unexpected behavior.\n".
            "Expects underlying resource will be closed.\n".
            'Expects underlying resource is not closed'
        );
    }
}
