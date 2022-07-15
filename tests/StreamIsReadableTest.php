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
class StreamIsReadableTest extends AbstractStreamTestCase
{
    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResourcesWithReadableState')]
    public function isReadableState($resource, bool $stateExpected): void
    {
        $stateCaught = (new Stream($resource))->isReadable();

        static::assertSame($stateExpected, $stateCaught);
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResources')]
    public function isReadableOnClosedStream($resource): void
    {
        $stream = new Stream($resource);
        $stream->close();

        static::assertFalse(
            $stream->isReadable(),
            'Expects closed stream is not readable'
        );
    }

    /**
     * @param resource $resource
     */
    #[Attributes\Test]
    #[Attributes\DataProvider('dataProviderResources')]
    public function isReadableOnDetachedStream($resource): void
    {
        $stream = new Stream($resource);
        $stream->detach();

        static::assertFalse(
            $stream->isReadable(),
            'Expects detached stream is not readable'
        );
    }

    public function dataProviderResourcesWithReadableState(): array
    {
        $result = [];

        foreach ($this->generateResources(AccessModeType::READABLE_ONLY) as $resource) {
            $result[] = [$resource, true];
        }
        foreach ($this->generateResources(AccessModeType::WRITABLE_ONLY) as $resource) {
            $result[] = [$resource, false];
        }
        foreach ($this->generateResources(AccessModeType::READABLE_AND_WRITABLE) as $resource) {
            $result[] = [$resource, true];
        }

        return $result;
    }
}
