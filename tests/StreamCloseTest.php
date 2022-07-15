<?php

declare(strict_types=1);

namespace HNV\Http\StreamTests;

use HNV\Http\Stream\Stream;
use PHPUnit\Framework\Attributes;

use function is_resource;

/**
 * @internal
 */
#[Attributes\CoversClass(Stream::class)]
#[Attributes\Small]
class StreamCloseTest extends AbstractStreamTestCase
{
    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResources')]
    public function close($resource): void
    {
        $stream = new Stream($resource);
        $stream->close();

        static::assertFalse(
            is_resource($resource),
            'Expects [close] operation WILL close underlying resource'
        );
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResources')]
    public function closeDetachedStream($resource): void
    {
        $stream             = new Stream($resource);
        $resourceDetached   = $stream->detach();
        $stream->close();

        static::assertTrue(
            is_resource($resourceDetached),
            'Expects [close] operation WILL NOT close detached resource'
        );
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResources')]
    public function destructorClosesResource($resource): void
    {
        $stream = new Stream($resource);
        unset($stream);

        static::assertFalse(
            is_resource($resource),
            'Expects stream underlying resource WILL close on stream destroy'
        );
    }
}
