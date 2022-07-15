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
class StreamIsWritableTest extends AbstractStreamTestCase
{
    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResourcesWithWritableState')]
    public function isWritableState($resource, bool $stateExpected): void
    {
        $stateCaught = (new Stream($resource))->isWritable();

        static::assertSame($stateExpected, $stateCaught);
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResources')]
    public function isWritableOnClosedStream($resource): void
    {
        $stream = new Stream($resource);
        $stream->close();

        static::assertFalse(
            $stream->isWritable(),
            'Expects closed stream is not writable'
        );
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResources')]
    public function isWritableOnDetachedStream($resource): void
    {
        $stream = new Stream($resource);
        $stream->detach();

        static::assertFalse(
            $stream->isWritable(),
            'Expects detached stream is not writable'
        );
    }

    public function dataProviderResourcesWithWritableState(): array
    {
        $result = [];

        foreach ($this->generateResources(AccessModeType::READABLE_ONLY) as $resource) {
            $result[] = [$resource, false];
        }
        foreach ($this->generateResources(AccessModeType::WRITABLE_ONLY) as $resource) {
            $result[] = [$resource, true];
        }
        foreach ($this->generateResources(AccessModeType::READABLE_AND_WRITABLE) as $resource) {
            $result[] = [$resource, true];
        }

        return $result;
    }
}
