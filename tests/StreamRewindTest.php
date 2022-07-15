<?php

declare(strict_types=1);

namespace HNV\Http\StreamTests;

use HNV\Http\Stream\Stream;
use PHPUnit\Framework\Attributes;
use RuntimeException;

use function ftell;

/**
 * @internal
 */
#[Attributes\CoversClass(Stream::class)]
#[Attributes\Small]
class StreamRewindTest extends AbstractStreamTestCase
{
    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResources')]
    public function rewind($resource): void
    {
        $stream = new Stream($resource);
        $stream->rewind();

        static::assertSame(0, ftell($resource));
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResources')]
    public function rewindOnClosedStream($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);
        $stream->close();
        $stream->rewind();

        static::fail('Expects exception on rewinding closed stream');
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResources')]
    public function rewindOnDetachedStream($resource): void
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream($resource);
        $stream->detach();
        $stream->rewind();

        static::fail('Expects exception on rewinding detached stream');
    }
}
