<?php

declare(strict_types=1);

namespace HNV\Http\StreamTests;

use HNV\Http\Stream\Stream;
use PHPUnit\Framework\Attributes;

/**
 * @internal
 */
#[Attributes\CoversClass(Stream::class)]
#[Attributes\Small]
class StreamDetachTest extends AbstractStreamTestCase
{
    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResources')]
    public function detach($resource): void
    {
        $stream = new Stream($resource);

        static::assertSame($resource, $stream->detach());
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResources')]
    public function detachClosedStream($resource): void
    {
        $stream = new Stream($resource);
        $stream->close();

        static::assertNull(
            $stream->detach(),
            'Expects null on closed stream'
        );
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResources')]
    public function detachDetachedStream($resource): void
    {
        $stream = new Stream($resource);
        $stream->detach();

        static::assertNull(
            $stream->detach(),
            'Expects null on detached stream'
        );
    }
}
