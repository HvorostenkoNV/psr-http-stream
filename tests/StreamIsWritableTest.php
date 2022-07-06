<?php

declare(strict_types=1);

namespace HNV\Http\StreamTests;

use HNV\Http\Helper\Collection\Resource\AccessModeType;
use HNV\Http\Stream\Stream;

/**
 * PSR-7 StreamInterface implementation test.
 *
 * Testing stream writable state info providing.
 *
 * @internal
 * @covers Stream
 * @small
 */
class StreamIsWritableTest extends AbstractStreamTest
{
    /**
     * @covers          Stream::isWritable
     * @dataProvider    dataProviderResourcesWithWritableState
     *
     * @param resource $resource recourse
     */
    public function testIsWritable($resource, bool $stateExpected): void
    {
        $stateCaught = (new Stream($resource))->isWritable();

        static::assertSame(
            $stateExpected,
            $stateCaught,
            "Action \"Stream->isWritable\" returned unexpected result.\n".
            "Expected result is \"{$stateExpected}\".\n".
            "Caught result is \"{$stateCaught}\"."
        );
    }

    /**
     * @covers          Stream::isWritable
     * @dataProvider    dataProviderResources
     *
     * @param resource $resource recourse
     */
    public function testIsWritableInClosedState($resource): void
    {
        $stream = new Stream($resource);
        $stream->close();

        static::assertFalse(
            $stream->isWritable(),
            "Action \"Stream->close->isWritable\" returned unexpected result.\n".
            "Expected result is \"false\".\n".
            'Caught result is "NOT false".'
        );
    }

    /**
     * @covers          Stream::isWritable
     * @dataProvider    dataProviderResources
     *
     * @param resource $resource recourse
     */
    public function testIsWritableInDetachedState($resource): void
    {
        $stream = new Stream($resource);
        $stream->detach();

        static::assertFalse(
            $stream->isWritable(),
            "Action \"Stream->detach->isWritable\" returned unexpected result.\n".
            "Expected result is \"false\".\n".
            'Caught result is "NOT false".'
        );
    }

    /**
     * Data provider: resources with their writable state.
     */
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
