<?php

declare(strict_types=1);

namespace HNV\Http\StreamTests;

use HNV\Http\Helper\Collection\Resource\AccessModeType;
use HNV\Http\Stream\Stream;
use PHPUnit\Framework\Attributes;

/**
 * @internal
 */
#[Attributes\CoversClass(Stream::class)]
#[Attributes\Small]
class StreamIsSeekableTest extends AbstractStreamTestCase
{
    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResourcesWithSeekingStateValues')]
    public function isSeekable($resource, bool $stateExpected): void
    {
        $stateCaught = (new Stream($resource))->isSeekable();

        static::assertSame($stateExpected, $stateCaught);
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResources')]
    public function isSeekableOnClosedStream($resource): void
    {
        $stream = new Stream($resource);
        $stream->close();

        static::assertFalse(
            $stream->isSeekable(),
            'Expects closed stream is not seekable'
        );
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResources')]
    public function isSeekableOnDetachedStream($resource): void
    {
        $stream = new Stream($resource);
        $stream->detach();

        static::assertFalse(
            $stream->isSeekable(),
            'Expects detached stream is not seekable'
        );
    }

    public function dataProviderResourcesWithSeekingStateValues(): array
    {
        $result = [];

        foreach ($this->generateResources(AccessModeType::ALL) as $resource) {
            $result[] = [$resource, true];
        }

        return $result;
    }
}
